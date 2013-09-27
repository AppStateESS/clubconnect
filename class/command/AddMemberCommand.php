<?php

/**
 * AddMemberCommand - Adds a member to an organization. Can be used by either SDR admins (forces the membership) or by club presidents (requests the membership).
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class AddMemberCommand extends Command {
    private $memberId;
    private $organizationId;
    private $roleId;

    function setMemberId($id){
        $this->memberId = $id;
    }

    function setOrganizationId($id){
        $this->organizationId = $id;
    }

    function setRoleId($id){
        $this->roleId = $id;
    }

    function getRequestVars()
    {
        $vars = array('action' => 'AddMember');
         
        if(isset($this->memberId)) {
            $vars['member_id'] = $this->memberId;
        }
         
        if(isset($this->organizationId)) {
            $vars['organization_id'] = $this->organizationId;
        }
         
        if(isset($this->roleId)) {
            $vars['role_id'] = $this->roleId;
        } else {
            $vars['role_id'] = 0;
        }

        return $vars;
    }

    function execute(CommandContext $context)
    {
        // Check permissions to even be here

        PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
        if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException(
                dgettext('sdr', GlobalLock::persistentMessage()));
        }

        if(!UserStatus::isUser() && !UserStatus::isAdmin()){
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied.');
        }

        if(!Term::isCurrentTermSelected() && !UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to modify SDR history.');
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        PHPWS_Core::initModClass('sdr', 'Member.php');
        
        $org    = new Organization($context->get('organization_id'));
        $orgMgr = new OrganizationManager($org);
        $mbr    = new Member($context->get('member_id'));
        
        // The command to redirect to when we're finished here
        $cmd = CommandFactory::getCommand('ShowOrganizationRoster');
        $cmd->setOrganizationId($org->getId());
        
        $orgMgr->ifLocked('You may not add members because ', $cmd);

        if(UserStatus::isAdmin()){
            // if admin, force student approval
            $studentApproved = 1;
            $force = true;
        }else{
            // if club officer, don't force approval
            $studentApproved = 0;
            $force = false;
        }

        if(UserStatus::isAdmin()) {
            $failure = sprintf(dgettext('sdr', 'Could not add %s to %s for %s: '),
                $mbr->getFullName(), $org->getName(), Term::getPrintableSelectedTerm());
            $success = sprintf(dgettext('sdr', '%s has been added to %s for %s.'),
                $mbr->getFullName(), $org->getName(), Term::getPrintableSelectedTerm());
        } else {
            $failure = sprintf(dgettext('sdr', 'Could not request %s to join %s: '),
                $mbr->getFullName(), $org->getName());
            $success = sprintf(dgettext('sdr', '%s has been sent a request to join %s.'),
                $mbr->getFullName(), $org->getName());
        }

        try {
            // Create the membership object
            $membership = $orgMgr->addMember($mbr,Term::getSelectedTerm(), $studentApproved, 1, $force);
            NQ::simple('sdr', SDR_NOTIFICATION_SUCCESS, $success);
        } catch(UnregisteredOrganizationException $uoe) {
            NQ::simple('sdr', SDR_NOTIFICATION_ERROR, $failure . $uoe->getMessage());
        } catch(CreateMembershipException $cme) {
            if(UserStatus::isAdmin()){
                PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
                $membership = MembershipFactory::getUserMembershipByOrganization($context->get('organization_id'), $mbr->getUsername());

                // Check if membership has already been approved
                if($membership->studentApproved() && $membership->organizationApproved()){
                    NQ::simple('sdr', SDR_NOTIFICATION_ERROR, $failure . $cme->getMessage());
                    $cmd->redirect();
                }

                // Membership is pending, approve them
                $membership->setOrganizationApproved(true);
                $membership->setOrganizationApproved(time());
                $membership->setStudentApproved(true);
                $membership->setStudentApprovedOn(time());
                $membership->setAdministrativeForce(true);

                $membership->save();
                
                $orgMgr->sendMemberAdditionEmail($mbr, $studentApproved);

                NQ::simple('sdr', SDR_NOTIFICATION_SUCCESS, $success);

            } else {
                NQ::simple('sdr', SDR_NOTIFICATION_ERROR, $failure . $cme->getMessage());
            }
        }
        $cmd->redirect();
    }
}

?>
