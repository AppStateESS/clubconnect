<?php

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');

class NoMembershipsMenu extends CommandMenu
{
    protected function setupCommands()
    {
		$this->addCommandByName('Find an organization to join', 'ClubDirectory');
		$this->addCommandByName('Manage your Co-Curricular Transcript', 'ShowUserTranscript');
	}
}

?>
