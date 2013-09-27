<?php

/**
 * SDR Controller
 * Initializes the SDR environment, determines user type, and sends
 * control off to another controller.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'ConfigurationManager.php');
PHPWS_Core::initModClass('sdr', 'Term.php');
PHPWS_Core::initModClass('sdr', 'UserStatus.php');
PHPWS_Core::initModClass('sdr', 'SDRSettings.php');
PHPWS_Core::initModClass('sdr', 'Command.php');
PHPWS_Core::initModClass('sdr', 'CommandContext.php');
PHPWS_Core::initModClass('sdr', 'CommandFactory.php');
PHPWS_Core::initModClass('sdr', 'SDRNotificationView.php');
PHPWS_Core::initModClass('sdr', 'GlobalLock.php');

abstract class SDR
{
    var $context;
    protected $activeCommand;

    public function __construct()
    {
        $this->context = CommandContext::getInstance();
    }

    public function getContext()
    {
        return $this->context;
    }

    public function process()
    {
        $cmd = null;

        $fac = CommandFactory::getInstance();
        $ctx = CommandContext::getInstance();

        try {
            $uri = $this->context->getUri();
            if(preg_match('/index.php/', $uri)) {
                $cmd = $fac->get($ctx->coalesce('action', 'Default'));
                //NQ::simple('sdr', SDR_NOTIFICATION_WARNING, 'Old Command Mechanism' . (array_key_exists('HTTP_REFERER', $_SERVER) ? ', referrer is ' . $_SERVER['HTTP_REFERER'] : '') . ', for command ' . $cmd->getAction());

                if($fac->reverseMap($cmd) !== FALSE) {
                    // Redirect to the URI-based version
                    $cmd->redirect();
                }
            } else {
                $cmd = CommandFactory::getInstance()->getByUri($this->context->getUri());
            }

            if(!$cmd->allowExecute()) {
                PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
                throw new PermissionException('Permission denied.');
            }
            $cmd->execute($this->context);
            $cmd->log();
            $this->activeCommand = $cmd;
        } catch (PermissionException $pe) {
            $this->context->pushContext($cmd);
            throw $pe;
        }
    }

    public static function silentNotify(Exception $e)
    {
        $message = "The following was an exception that was not reported to the\nuser, and should not have caused any serious issues.\nLikely is an indication of Banner garbage.\n\n" . self::formatException($e);
        self::emailError($message, 'Silent Uncaught Exception');
    }

    public static function formatException(Exception $e)
    {
        ob_start();
        echo "Ohes Noes!  SDR threw an exception that was not caught!\n\n";
        echo "Here is CurrentUser:\n\n";
        print_r(Current_User::getUserObj());
        echo "\n\nHere is the exception:\n\n";
        print_r($e);
        if(isset($this)) {
            echo "\n\nHere is the CommandContext:\n\n";
            print_r($this->context);
        } else {
            echo "\n\nNo CommandContext to report on.";
        }
        echo "\n\nHere is $_REQUEST:\n\n";
        print_r($_REQUEST);
        $message = ob_get_contents();
        ob_end_clean();

        return $message;
    }

    public static function emailError($message, $subject)
    {
        PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
        $to = SDRSettings::getUberAdminEmail();

        if(is_null($to) || empty($to)) {
            throw new Exception('No Uber Admin Email was set.  Please check SDR Global Settings.');
        }
        $email = new EmailMessage($to, 'sdr_system', $to, NULL, NULL, NULL, $subject, 'email/admin/UncaughtException.tpl');

        $email_tags = array('MESSAGE' => $message);

        $email->setTags($email_tags);
        $email->send();
    }

    protected function saveState()
    {
        if(isset($_SESSION['SDR_No_Push_Context'])) {
            unset($_SESSION['SDR_No_Push_Context']);
        } else {
            $this->context->pushContext($this->activeCommand);
        }
    }

    public static function quit()
    {
        NQ::close();
        exit();
    }

    public static function throwDb($result)
    {
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return $result;
    }
}

?>
