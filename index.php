<?php

spl_autoload_register('sdr_autoload');

PHPWS_Core::initModClass('sdr', 'NamespaceUtils.php');

function sdr_autoload($class)
{
    if(substr($class, 0, 4) == 'sdr\\') {
        $path = \sdr\NamespaceUtils::namespaceToPath($class);
    } else {
        $path = $class;
    }
    \PHPWS_Core::initModClass('sdr', $path . '.php');
}

PHPWS_Core::initModClass('sdr', 'SDRFactory.php');
$controller = SDRFactory::getSDR();

function formatException(Exception $e)
{
    ob_start();
    echo "Ohes Noes!  SDR threw an exception that was not caught!\n\n";
    echo "Host: {$_SERVER['SERVER_NAME']}({$_SERVER['SERVER_ADDR']})\n";
    echo 'Request time: ' . date("D M j G:i:s T Y", $_SERVER['REQUEST_TIME']) . "\n";
    if(isset($_SERVER['HTTP_REFERER'])){
        echo "Referrer: {$_SERVER['HTTP_REFERER']}\n";
    }else{
        echo "Referrer: (none)\n";
    }
    echo "Remote addr: {$_SERVER['REMOTE_ADDR']}\n\n";
    echo "Query String: {$_SERVER['REQUEST_URI']}\n\n";
    echo "\n\nHere is $_SERVER:\n\n";
    print_r($_SERVER);
    echo "\n\nHere is the exception:\n\n";
    print_r($e);
    echo "\n\nHere is $_REQUEST:\n\n";
    print_r($_REQUEST);
    echo "Here is CurrentUser:\n\n";
    print_r(Current_User::getUserObj());
    if(array_key_exists('SDR_Last_Context', $_SESSION)) {
        echo "\n\nHere is the saved CommandContext:\n\n";
        print_r($_SESSION['SDR_Last_Context']);
    }
    $message = ob_get_contents();
    ob_end_clean();

    return $message;
}

function emailError($message, $subject)
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

function logError($message, $subject)
{
    $head = "Subject: $subject\n";
    PHPWS_Core::log($head . $message, 'sdr_exception.log');
}

try {
    javascript('jquery');
    $controller->process();
} catch(Exception $e) {
    
    PHPWS_Core::initModClass('sdr','SDRSettings.php');
    if(SDRSettings::getExceptionTestFlag()){
        // Test flag is set, so just re-throw the exception and let PHP handle it
        throw $e;
        return;
    }
   
    try {
        // Format the exception nicely and email it
        $message = formatException($e);
        NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'An internal error has occurred, and the authorities have been notified.  We apologize for the inconvenience.');
        logError($message, 'Uncaught Exception');
        emailError($message, 'Uncaught Exception');
        CommandContext::getInstance()->errorBack();
    } catch (Exception $e) {
        throw $e;
        $message2 = formatException($e);
        echo "SDR has experienced a major internal error.  Attempting to email an admin and then exit.";
        $messaage = "Something terrible has happened, and the exception catch-all threw an exception.\n\nThe second exception was:\n\n$message2";
        mail('webmaster@tux.appstate.edu', 'A Major SDR Error Has Occurred', $message2);
        logError($message2, 'EXCEPTION CATCH-ALL FAILURE');
        exit();
    }
}

spl_autoload_unregister('sdr_autoload');
?>
