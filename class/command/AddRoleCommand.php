<?php

  /**
   * AddRoleCommand - Adds a role to a particular membership
   * @author Jeremy Booker
   */

PHPWS_Core::initModClass('sdr', 'Command.php');

class AddRoleCommand extends Command {
    private $membershipId;
    private $roleId;
    
    function setMembershipId($id){
        $this->membershipId = $id;
    }
    
    function setRoleId($id){
        $this->roleId = $id;
    }
    
    function getRequestVars()
    {
        return array('action'=>'AddRole', 'membership_id'=>$this->membershipId, 'role_id'=>$this->roleId);
    }
    
    function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
        if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException(
                                          dgettext('sdr', GlobalLock::persistentMessage()));
        }

        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        PHPWS_Core::initModClass('sdr', 'MembershipRole.php');
        PHPWS_Core::initModClass('sdr', 'Role.php');

        
        $membership_id  = $context->get('membership_id');
        $role_id        = $context->get('role_id');
        
        $membership = MembershipFactory::getMembershipByIdWithRoles($membership_id);
        // lookup the role's title
        $role = new Role($role_id);

        // check if role is hidden
        if(!$role->getHidden()){

# Make sure the user has permission to execute this command
            if(!UserStatus::orgAdmin($membership->getOrganizationId())){
                PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
                throw new PermissionException('Permission denied.');
            }
            if(!Term::isCurrentTermSelected() && !UserStatus::isAdmin()) {
                PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
                throw new PermissionException('You do not have permission to modify SDR history.');
            }
        
            $member_role = new MembershipRole($membership_id, $role_id);
            $member_role->save();
        
            // Send an email to the student letting them know the role was changed
            PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
            $email = new EmailMessage($membership->getMember()->getUsername(),'sdr_system', $membership->getMember()->getUsername() . '@appstate.edu', NULL, NULL, NULL, 'Club role change notification','email/student/roleChangeNotification.tpl');
            $email_tags['NAME'] = $membership->getMember()->getFullName();
            $email_tags['ORG_NAME'] = $membership->getOrganizationName(false);
            $email_tags['ADDED_ROLE_TITLE'] = $role->getTitle();
            if(!Term::isCurrentTermSelected()) {
                $email_tags['TERM'] = Term::getPrintableSelectedTerm();
            }
        
            $email->setTags($email_tags);
        
            $email->send();

            $context->setContent(array('role_id'=>$role_id, 'title'=>$role->getTitle()));
        } else {
            // If role is hidden let the user know
            NQ::simple('sdr', SDR_NOTIFICATION_ERROR, 'You cannot assign a hidden role!');
            CommandContext::goBack();
        }
    }
}

?>
