<?php

/**
 * Accepts a membership which is awaiting approval by a student
 * @author Jeff Tickle
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class AcceptMembershipCommand extends Command {
    
    public $membershipId;
    
    function setMembershipId($id){
        $this->membershipId = $id;
    }
    
    function getRequestVars()
    {
        $vars = array('action' => 'AcceptMembership');
    	
        if(isset($this->membershipId)) {
           $vars['membership_id'] = $this->membershipId;
        }
    	
        return $vars;
    }
    
    function execute(CommandContext $context)
    {
        if(isset($this->membershipId)) {
            $membership_id = $this->membershipId;
        } else {
            $membership_id = $context->get('membership_id');
        }
        
        if(is_null($membership_id) || !isset($membership_id)){
            throw new InvalidArgumentException('No Membership specified to AcceptMembershipCommand');
        }
        
        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        $membership = MembershipFactory::getMembershipById($membership_id);
        
        $cmd = CommandFactory::getCommand('ShowUserSummary');
        
        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        $manager = new OrganizationManager($membership->getOrganizationId());
        $manager->ifLocked('You may not accept membership in this organization because ', $cmd);
        
        $membership->setStudentApproved(1);
        $membership->setStudentApprovedOn(time());
        $membership->save();
        
        // TODO: Email club admins with the good news
        /*
        PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
        $email = new EmailMessage(
            $membership->getStudentUsername(),
            'sdr_system',
            $membership->getStudentUsername() . '@appstate.edu',
            NULL, NULL, NULL,
            dgettext('sdr', 'Club Membership Request Approved'),
            'email/student/approveMembershipNotification.tpl');
            
        $email_tags = array();
        $email_tags['NAME'] = $membership->getStudentName();
        $email_tags['ORG_NAME'] = $membership->getOrganizationName();
        
        $email->setTags($email_tags);
        $email->send();*/
        
        NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS,
            sprintf(dgettext('sdr', 'You are now a member of %s.'),
                $membership->getOrganizationName()));
        $cmd->redirect();
    }
}
?>
