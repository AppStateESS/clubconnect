<?php

/**
 * Shows the Organization Roster
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowOrganizationRosterCommand extends Command
{
	protected $organization_id;
	
    function getParams()
    {
        return array('organization_id');
    }
	
	function setOrganizationId($id)
	{
		$this->organization_id = $id;
	}
	
	function getJsCallback(){
	    return 'ShowOrganizationRoster';
	}
	
	function execute(CommandContext $context)
	{
		if(!isset($this->organization_id)) {
			$this->organization_id = $context->get('organization_id');
		}
		
		$orgid = $this->organization_id;
		
		if(!UserStatus::orgAdmin($orgid)) {
			PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
			throw new PermissionException('You do not have permission to browse this organization\'s roster.');
		}
		
		PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
		$orgmanager = new OrganizationManager($orgid);
		
		$context->setContent($orgmanager->showRoster());
	}
}
