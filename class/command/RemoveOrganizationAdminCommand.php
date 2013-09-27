<?php

/**
 * RemoveOrganizationAdminCommand - Changes a member to the "admin" flag for a particular organization
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class RemoveOrganizationAdminCommand extends Command {
    private $membershipId;
    
    function setMembershipId($id){
        $this->membershipId = $id;
    }
    
    function setRoleId($id){
        $this->roleId = $id;
    }
    
    function getRequestVars()
    {
        return array('action'=>'RemoveOrganizationAdmin', 'membership_id'=>$this->membershipId);
    }
    
    function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        PHPWS_Core::initModClass('sdr', 'MembershipRole.php');
        PHPWS_Core::initModClass('sdr', 'Role.php');
        PHPWS_Core::initModClass('sdr', 'Term.php');
        
        $membership_id  = $context->get('membership_id');
        
        $membership = MembershipFactory::getMembershipById($membership_id);
        
        // Make sure the user has permission to execute this command
        if(!UserStatus::isAdmin()) {
        	PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
        	throw new PermissionException('You do not have permission to modify organization administrators.');
        }
        
        // Remove Administrator Flag
        $membership->setAdministrator(0);
        $membership->save();
        
        $context->setContent(array('membership_id'=>$membership_id));
    }
}

?>
