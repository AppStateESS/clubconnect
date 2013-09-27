<?php

/**
 * Shows Administrative Settings
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class AdminSettingsCommand extends CrudCommand
{
    public function allowExecute()
    {
        return UserStatus::hasPermission('settings');
    }
	
	public function get(CommandContext $context)
	{
		PHPWS_Core::initModClass('sdr', 'SDRSettings.php');
		
		$settings = new SDRSettings();
		
		$context->setContent($settings->showEditForm($this));
	}

    public function post(CommandContext $context)
    {
		if(!UserStatus::isAdmin()) {
			PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
			throw new PermissionException('I see you trying to do that!');
		}
		
		SDRSettings::saveFromContext($context);
		
		NQ::simple('sdr', SDR_NOTIFICATION_SUCCESS, 'SDR Global Settings have been saved.');
		
		$this->redirect();
    }
}
