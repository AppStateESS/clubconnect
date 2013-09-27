<?php

/**
 * Approves a membership which is awaiting approval by a club officer
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'LockableCommand.php');

class ApproveMembershipCommand extends LockableCommand {
    
    public $membershipId;
    private $organizationId;
    
    function setMembershipId($id){
        $this->membershipId = $id;
    }
    
    function setOrganizationId($id){
        $this->organizationId = $id;
    }
    
    function getRequestVars()
    {
        return array('action' => 'ApproveMembership', 'membership_id' => $this->membershipId, 'organization_id' => $this->organizationId);
    }
    
    function execute(CommandContext $context)
    {
      PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
      if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
	PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
	throw new PermissionException(
				      dgettext('sdr', GlobalLock::persistentMessage()));
      }
        if(isset($this->membershipId)) {
        	$membership_id = $this->membershipId;
        } else {
            $membership_id = $context->get('membership_id');
        }
        
        if(is_null($membership_id) || !isset($membership_id)){
            throw new InvalidArgumentException('No Membership specified to ApproveMembershipCommand');
        }

        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        $membership = MembershipFactory::getMembershipById($membership_id);
        
        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        $manager = new OrganizationManager($membership->getOrganizationId());
        $manager->ifLocked('You may not approve membership requests because ');
        
        $membership->setOrganizationApproved(1);
        $membership->save();
        
        PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
        $email = new EmailMessage(
            $membership->getMember()->getUsername(),
            'sdr_system',
            $membership->getMember()->getUsername() . '@appstate.edu',
            NULL, NULL, NULL,
            dgettext('sdr', 'Club Membership Request Approved'),
            'email/student/approveMembershipNotification.tpl');
            
        $email_tags = array();
        $email_tags['NAME'] = $membership->getMember()->getFullName();
        $email_tags['ORG_NAME'] = $membership->getOrganizationName(false);
        
        $email->setTags($email_tags);
        $email->send();

        NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS,
            sprintf(dgettext('sdr', 'Approved request from %s to join %s for %s.'),
            $membership->getMember()->getFullName(), $membership->getOrganizationName(),
            Term::toString($membership->getTerm())));
        
        $successCmd = CommandFactory::getCommand('ShowOrganizationRoster');
        $successCmd->setOrganizationId($membership->getOrganizationId());
        $successCmd->redirect();
    }
}
?>
