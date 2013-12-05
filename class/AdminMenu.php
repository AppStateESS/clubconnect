<?php

/**
 * SDR Admin Menu Controller
 * Displays a side menu based on permissions.
 * 
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'PersistentAdminMenu.php');

class AdminMenu extends PersistentAdminMenu
{
	public function setupCommands()
	{
		$this->addCommandByName('Summary', 'ShowAdminSummary');
		$this->addCommandByName('Club Directory', 'ClubDirectoryCommand');
		$this->addCommandByName('Students and Advisors', 'PeopleCommand');
        parent::setupCommands();
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
                    
		return PHPWS_Template::process($tpl, 'sdr', 'UserMenu.tpl');
	}
}
