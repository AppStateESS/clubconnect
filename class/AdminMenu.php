<?php

/**
 * SDR Admin Menu Controller
 * Displays a side menu based on permissions.
 * 
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');

class AdminMenu extends CommandMenu
{
	public function __construct()
	{
		parent::__construct();
		
		PHPWS_Core::initModClass('sdr', 'TranscriptRequest.php');
		$requests = TranscriptRequest::countPending();
		
		$request = '';
		if($requests > 0) {
			$request = " ($requests)";
		}

        PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationController.php');
        $ctrl = new OrganizationRegistrationController();
        $apps = $ctrl->countPending();

        $app = '';
        if($apps > 0) {
            $app = " ($apps)";
        }

		$this->addCommandByName('Summary', 'ShowAdminSummary');
		$this->addCommandByName('Club Directory', 'ClubDirectoryCommand');
		$this->addCommandByName('Students and Advisors', 'PeopleCommand');
		$this->addCommandByName('Edit Roles', 'EditRoles');
		$this->addCommandByName("Transcript Requests$request", 'ShowTranscriptRequests');
        $this->addCommandByName("Club Registrations$app", 'ShowOrganizationApplications');
		$this->addCommandByName('Run Reports', 'ListReports');
		$this->addCommandByName('Rollover', 'ShowRollover');
		$this->addCommandByName('Settings', 'AdminSettingsCommand');
		$this->addCommandByName('Global Lock', 'GlobalLockCommand');
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
                    
		return PHPWS_Template::process($tpl, 'sdr', 'UserMenu.tpl');
	}
}
