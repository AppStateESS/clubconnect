<?php

namespace sdr;
use \PHPWS_Core;
use \Exception;
use \spl_autoload_register;
use \spl_autoload_unregister;
use \SDRSettings;
use \EmailMessage;
use \NQ;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class Environment
{
    protected static $INSTANCE;

    public static function getInstance()
    {
        if(!isset(self::$INSTANCE)) {
            self::$INSTANCE = new Environment();
        }

        return self::$INSTANCE;
    }

    protected $nsutil;
    protected $autoload;

    public function __construct()
    {
        \PHPWS_Core::initModClass('sdr', 'NamespaceUtils.php');
        $this->nsutil = new NamespaceUtils();

        $this->autoload = array($this, 'autoload');
    }

    public function autoload($class)
    {
        if(substr($class, 0, 4) == 'sdr\\') {
            $path = $this->nsutil->namespaceToPath($class);
        } else {
            $path = $class;
        }
        \PHPWS_Core::initModClass('sdr', $path . '.php');
    }

    public function bootstrap()
    {
        \spl_autoload_register($this->autoload);
    }

    public function unbootstrap()
    {
        \spl_autoload_unregister($this->autoload);
    }

    public function makeExceptionId()
    {
        return strtoupper(bin2hex(openssl_random_pseudo_bytes(4)));
    }

    public function silentException(Exception $e)
    {
        $unique = $this->makeExceptionId();
        $this->handleException($e, "[ID:$unique] Silent Uncaught Exception");
    }

    public function handleException(Exception $e, $subject)
    {
        \PHPWS_Core::initModClass('sdr', 'SDRSettings.php');

        if(\SDRSettings::getExceptionTestFlag()) {
            var_dump($e);
            return;
        }

        // Generate Exception ID
        $unique = $this->makeExceptionId();
        $subject = "[ID:$unique] $subject";

        try {
            $message = $this->formatException($e, $unique);
            \NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, SDRSettings::getExceptionMessage());
            $this->logError($message, $subject);
            $this->emailError($message, $subject);
        } catch(Exception $e) {
            if(\SDRSettings::getExceptionTestFlag()) {
                $message2 = formatException($e);
                echo "ClubConnect has experienced an internal error.  Attempting to email an admin and then BAIL OUT!";
                $message = "Something terrible has happened, and the exception catch-all threw an exception.\n\nThe first exception was:\n\n$message\n\nThe second exception was:\n\n$message2";
                mail('webmaster@tux.appstate.edu', 'A Major SDR Error Has Occurred', $message2);
                logError($message, 'THE WORST KIND OF EXCEPTION');
                exit();
            }
        }

        return $unique;
    }

    public function formatException(Exception $e, $unique)
    {
        ob_start();
        echo "Ohes Noes!  SDR threw an exception that was not caught!\n\n";
        echo "This exception has unique ID $unique\n\n";
        echo "Host: {$_SERVER['SERVER_NAME']}({$_SERVER['SERVER_ADDR']})\n";
        echo 'Request time: ' . date("D M j G:i:s T Y", $_SERVER['REQUEST_TIME']) . "\n";
        if(isset($_SERVER['HTTP_REFERER'])){
            echo "Referrer: {$_SERVER['HTTP_REFERER']}\n";
        }else{
            echo "Referrer: (none)\n";
        }
        echo "Remote addr: {$_SERVER['REMOTE_ADDR']}\n\n";
        echo "Query String: {$_SERVER['REQUEST_URI']}\n\n";
        echo '\n\nHere is $_SERVER:\n\n';
        json_encode($_SERVER);
        print_r($_SERVER);
        echo "\n\nHere is the exception:\n\n";
        print_r($e);
        echo '\n\nHere is $_REQUEST:\n\n';
        print_r($_REQUEST);
        echo "Here is CurrentUser:\n\n";
        print_r(\Current_User::getUserObj());
        if(array_key_exists('SDR_Last_Context', $_SESSION)) {
            echo "\n\nHere is the saved CommandContext:\n\n";
            print_r($_SESSION['SDR_Last_Context']);
        }
        $message = ob_get_contents();
        ob_end_clean();

        return $message;
    }

    public function emailError($message, $subject)
    {
        \PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
        $to = \SDRSettings::getUberAdminEmail();

        if(is_null($to) || empty($to)) {
            throw new \Exception('No Uber Admin Email was set.  Please check SDR Global Settings.');
        }
        $email = new \EmailMessage($to, 'sdr_system', $to, NULL, NULL, NULL, $subject, 'email/admin/UncaughtException.tpl');

        $email_tags = array('MESSAGE' => $message);

        $email->setTags($email_tags);
        $email->send();
    }

    public function logError($message, $subject)
    {
        $head = "Subject: $subject\n";
        \PHPWS_Core::log($head . $message, 'sdr_exception.log');
    }

    public function errorBack()
    {
        \CommandContext::getInstance()->errorBack();
    }

    public function cleanExit()
    {
        \NQ::close();
        session_write_close();
        exit();
    }

}

?>
