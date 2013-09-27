<?php

/**
 * SDR Organization Manager Controller
 * The Organization Manager.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Organization.php');
PHPWS_Core::initModClass('sdr', 'OrganizationView.php');

class OrganizationManager
{
    private $organization;

    public function __construct($organization)
    {
        if(is_a($organization, 'Organization')) {
            $this->organization = $organization;
        } else {
            $this->organization = new Organization($organization);
        }
    }

    private function encapsulateView($content)
    {
        $view = new OrganizationView($this->organization);
        $view->setMain($content);
        return $view->show();
    }

    function showRoster()
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationRoster.php');
         
        $roster = new OrganizationRoster($this->organization);
         
        return $this->encapsulateView($roster->show());
    }

    function showMessaging()
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationMessaging.php');

        $messaging = new OrganizationMessaging($this->organization);

        return $this->encapsulateView($messaging->show());
    }

    function showHistory()
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationHistory.php');

        $history = new OrganizationHistory($this->organization);

        return $this->encapsulateView($history->show());
    }

    function showProfile()
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationProfile.php');
        PHPWS_Core::initModClass('sdr', 'OrganizationProfileController.php');

        $profile = OrganizationProfile::getByOrganizationId(
        $this->organization->id);
        $profile->loadOrganization($this->organization);

        $controller = new OrganizationProfileController();
        $controller->setOrganizationProfile($profile);

        return $controller->view();
    }

    function editProfile()
    {

      PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
      if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
	PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
	throw new PermissionException(
				      dgettext('sdr', GlobalLock::persistentMessage()));
      }

        PHPWS_Core::initModClass('sdr', 'OrganizationProfile.php');
        PHPWS_Core::initModClass('sdr', 'OrganizationProfileController.php');

        $profile = OrganizationProfile::getByOrganizationId($this->organization->getId());
        $profile->loadOrganization($this->organization);

        $controller = new OrganizationProfileController($profile);

        return $this->encapsulateView($controller->edit());
    }

    function showSettings()
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationSettingsView.php');
        $settingsView = new OrganizationSettingsView($this->organization);

        return $this->encapsulateView($settingsView->show());
    }

    function showChangeRoles(Membership $membership)
    {
        $term = Term::getSelectedTerm();

        PHPWS_Core::initModClass('sdr', 'ChangeRolesView.php');
        $rolesView = new ChangeRolesView($membership, $term);

        return $this->encapsulateView($rolesView->show());
    }

    public function showRename()
    {
        PHPWS_Core::initModClass('sdr', 'RenameOrganizationView.php');
        $renameView = new RenameOrganizationView($this->organization);
        
        return $this->encapsulateView($renameView->show());
    }

    /**
     * Adds a member to this organization (by creating a membership). Also sends notification emails to students/admins, and optionally creates roles.
     *
     * @param $member Member object for the member to add
     * @param $term term the membership should be created for
     * @param $studentApproved Set to 1 if the student has confirmed this membership (or if administratively adding a member)
     * @param $organizationApproved Set to 1 if the organization has approved this membership, set to 0 when a student is requesting membership
     * @param $roleId Optional. The id of the Role to create for this membership.
     * @return Membership Returns the membership object which was created. (A simple member object, without the extra data added by the MembershipFactory.)
     * @throws CreateMembershipException
     * @throws DatabaseException
     */
    function addMember(Member $member, $term, $studentApproved, $organizationApproved, $administrative_force = false, $roleId = NULL){
        PHPWS_Core::initModClass('sdr', 'Membership.php');
        PHPWS_Core::initModClass('sdr', 'Role.php');
        PHPWS_Core::initModClass('sdr', 'MembershipRole.php');
        
        // Can't request from an unregistered organization
        if(!$this->organization->registeredForTerm($term)) {
            PHPWS_Core::initModClass('sdr', 'exception/UnregisteredOrganizationException.php');
            throw new UnregisteredOrganizationException('You can not request membership in an unregistered organization.');
        }

        // Can't request membership if not logged in
        if(UserStatus::isGuest()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You must log in to request membership in an organization.');
        }

//        if($this->organization->isGreek()) {
//            $membership = Membership::createGreekNewMembership($member, $this->organization->getId(), $term, $studentApproved, $organizationApproved, $administrative_force);
            // TODO: Shoot me for doing this.  Short notice hurts.  I'm so sorry that you have to fix whatever problem this caused.
//            $mr = new MembershipRole($membership, 32);
//            $mr->save();
//        } else {
            $membership = Membership::createMembership($member, $this->organization->getId(), $term, $studentApproved, $organizationApproved, $administrative_force);

            if(!is_null($roleId)) {
                $mr = new MembershipRole($membership, $roleId);
                $mr->save();
            }
            
            $this->sendMemberAdditionEmail($member, $studentApproved);
//        }
        
        return $membership;
    }

    function sendMemberAdditionEmail(Member $member, $studentApproved)
    {
        PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
        if($studentApproved == 1){
            // Forced student approval, an admin must have added this membership
            $email = new EmailMessage($member->getUsername(),'sdr_system', $member->getUsername() . '@appstate.edu', NULL, NULL, NULL, 'Club membership established','email/student/addMembershipNotification.tpl');
        }else{
            $email = new EmailMessage($member->getUsername(),'sdr_system', $member->getUsername() . '@appstate.edu', NULL, NULL, NULL, 'Club membership requested','email/student/requestMembershipNotification.tpl');
        }
        
        $email_tags['NAME']     = $member->getFullName();
        $email_tags['ORG_NAME'] = $this->organization->getName(false);
        
        if(!Term::isCurrentTermSelected()) {
            $email_tags['TERM'] = Term::getPrintableSelectedTerm();
        }
        
        $email->setTags($email_tags);
        $email->send();
    }

    function requestMembership(Member $member)
    {
        PHPWS_Core::initModClass('sdr', 'Membership.php');
        PHPWS_Core::initModClass('sdr', 'Term.php');

        try{
            $membership = Membership::createMembership($member, $this->organization->getId(), Term::getCurrentTerm(), 1, 0);
        } catch (SDRException $e){
            // rethrow it
            throw $e;
            return;
        }
        
        return $membership;
    }
    
    public function ifLocked($message, $cmd = null)
    {
        $org = $this->organization;
        if($org->getLocked() && !UserStatus::isAdmin()) {
            $reason = $org->getReasonAccessDenied();
            $name = $org->getName();
            if(empty($reason)) $reason = 'this organization has been administratively locked.';
            $message = 'You may not accept membership in '.$name.' because ' . $reason;
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, $message);
            if(is_null($cmd)) $cmd = CommandFactory::getCommand('GoBack');
            $cmd->redirect();
        }
    }
    
    public function getOrganization()
    {
        return $this->organization;
    }
}

?>
