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

        if(!SDRSettings::hasConfigured()) {
            $cmd = $fac->get('AdminSettingsCommand');
            NQ::simple('sdr', SDR_NOTITFICATION_WARNING, 'ClubConnect is unconfigured, and you will not be able to do anything else until you save these settings.');
            $cmd->execute($this->context);
            $this->activeCommand = $fac->get('AdminSettingsCommand');
            return;
        }

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

        $this->activeCommand = $cmd;

        if(!$cmd->allowExecute()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied.');
        }
        $cmd->execute($this->context);
        $cmd->log();
    }

    protected function saveState()
    {
        if(isset($_SESSION['SDR_No_Push_Context'])) {
            unset($_SESSION['SDR_No_Push_Context']);
        } else {
            $this->context->pushContext($this->activeCommand);
        }
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
