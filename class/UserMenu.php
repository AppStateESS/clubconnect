<?php

/**
 * SDR User Menu Controller
 * Displays a side menu based on permissions.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');

class UserMenu extends CommandMenu
{
    protected function setupCommands()
	{
		$this->addCommandByName('Home', 'ShowUserSummary');
        $this->addCommandByName('Club Directory', 'ClubDirectory');
        //$this->addCommandByName('Club Registration', 'ClubRegistrationFormCommand');
		$this->addCommandByName('Manage Transcript', 'ShowUserTranscript');
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		    		
		return PHPWS_Template::process($tpl, 'sdr', 'UserMenu.tpl');
	}
}

?>
