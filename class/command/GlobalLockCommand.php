<?php

  /**
   * Show the Global Lock Options
   *
   * @author Robert Bost <bostrt at appstate dot edu>
   */

PHPWS_core::initModClass('sdr', 'CrudCommand.php');

class GlobalLockCommand extends CrudCommand
{
    public function allowExecute()
    {
        return UserStatus::hasPermission('global_lock');
    }

    public function get(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'GlobalLock.php');

        $global = new GlobalLock();

        $context->setContent($global->show($this));
    }

    public function post(CommandContext $context)
    {
        if(!Current_User::isDeity()){
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('Don\'t do that!');
        }
        if(!is_null($context->get('global_lock'))){
            if($context->get('global_lock') == 'lock'){
                SDRSettings::setGlobalLock($context->get('global_lock'));
                NQ::simple('sdr', SDR_NOTIFICATION_WARNING, 'SDR Global Lock is enabled');
            } 
            else{
                SDRSettings::setGlobalLock($context->get('global_lock'));
                NQ::simple('sdr', SDR_NOTIFICATION_SUCCESS, 'SDR Global Lock is disabled');
            }
            // If message is set
            if(!is_null($context->get('global_lock_message'))){
                SDRSettings::setGlobalLockMessage($context->get('global_lock_message'));
            } else {
                // Else, set to a default message.
                SDRSettings::setGlobalLockMessage('Global Lock is enabled. You cannot do that right now.');
            }
        } else {
            NQ::simple('sdr', SDR_NOTIFICATION_ERROR, 'You probably shouldn\'t do that');
        }

        $this->redirect();
    }
}
?>
