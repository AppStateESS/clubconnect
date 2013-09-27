<?php

/**
 * AddOrganizationAdminCommand - Changes a member to an "admin" for a particular organization
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class AddOrganizationAdminCommand extends Command {
    private $membershipId;
    
    function setMembershipId($id){
        $this->membershipId = $id;
    }
    
    function setRoleId($id){
        $this->roleId = $id;
    }
    
    function getRequestVars()
    {
        return array('action'=>'AddOrganizationAdmin', 'membership_id'=>$this->membershipId);
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
        
        // Set Administrator Flag
        $membership->setAdministrator(1);
        $membership->save();
        
        $context->setContent(array('membership_id'=>$membership_id));
    }
}

?>
