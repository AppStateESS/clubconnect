<?php

/**
 * Shows the Interface for Changing User Roles
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'LockableCommand.php');

class ShowChangeRolesCommand extends LockableCommand
{
	private $membershipId;
	
	function getRequestVars()
	{
		$vars = array('action' => 'ShowChangeRoles');
		
		if(isset($this->membershipId)) {
			$vars['membership_id'] = $this->membershipId;
		}
		
		return $vars;
	}
	
	function setMembershipId($id)
	{
		$this->membershipId = $id;
	}
	
	function execute(CommandContext $context)
	{
	  PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
	  if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
	    PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
	    throw new PermissionException(
					  dgettext('sdr', GlobalLock::persistentMessage()));
	  }
	  if(!Term::isCurrentTermSelected() && !UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to modify SDR history.');
	  }
	    
	    PHPWS_Core::initModClass('sdr', 'Membership.php');
	    PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
	    
	    // Create the membership object
	    PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
	    $membership = MembershipFactory::getMembershipByIdWithRoles($context->get('membership_id'));
	    
	    $orgmanager = new OrganizationManager($membership->getOrganizationId());
	    
	    $context->setContent($orgmanager->showChangeRoles($membership));
	}
}