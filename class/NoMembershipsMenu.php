<?php

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');

class NoMembershipsMenu extends CommandMenu
{
	public function __construct()
	{
		parent::__construct();
		
		$this->addCommandByName('Find an organization to join', 'ClubDirectory');
		$this->addCommandByName('Manage your Co-Curricular Transcript', 'ShowUserTranscript');
	}
}

?>
