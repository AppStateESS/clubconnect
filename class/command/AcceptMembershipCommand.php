<?php

/**
 * Accepts a membership which is awaiting approval by a student
 * @author Jeff Tickle
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class AcceptMembershipCommand extends CrudCommand {
    
    public $membership_id;

    public function getParams()
    {
        return array('membership_id');
    }

    public function setMembershipId($id)
    {
        $this->membership_id = $id;
    }

    public function get(CommandContext $context)
    {
        $membership = new Membership($this->membership_id);
        $org = $membership->getOrganization();
        $orgMgr = new OrganizationManager($org);

        $orgMgr->ifLocked('You may not accept membership in this organization because ');

        $summaryCmd = CommandFactory::getInstance()->getCommand('ShowUserSummaryCommand');

        $vars = array(
            'FULLNAME'   => $org->getName(false),
            'TERM'       => Term::getCurrentTerm(),
            'ACCEPT'     => $this->getURI(),
            'CANCEL'     => $summaryCmd->getURI(),
            'AGREEMENTS' => array(array(
                'CONTENT' => $org->getAgreement()))
        );

        $context->setContent(PHPWS_Template::process(
            $vars, 'sdr', 'AcceptMembership.tpl'));
    }
   
    public function post(CommandContext $context)
    {
        $membership_id = $this->membership_id;
        
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
