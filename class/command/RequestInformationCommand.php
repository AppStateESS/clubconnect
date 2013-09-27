<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class RequestInformationCommand extends Command {
	
	private $organizationId;
	
	function getRequestVars()
	{
		$vars = array('action' => 'RequestInformation');
		
		if(isset($this->organizationId)) {
			$vars['organization_id'] = $this->organizationId;
		}
		
		return $vars;
	}
    
    public function setOrganizationId($orgid)
    {
    	$this->organizationId = $orgid;
    }
    
    public function execute(CommandContext $context)
    {
	PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
	if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
	  PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
	  throw new PermissionException(
					dgettext('sdr', GlobalLock::persistentMessage()));
	}

        PHPWS_Core::initModClass('sdr', 'Organization.php');
        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
        
        $org = new Organization($context->get('organization_id'));
        $mbr = new Member(null, UserStatus::getUsername());

	// Check permissions and user status
        if(UserStatus::isGuest()) {
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'You must log in using the link below before you can request information about this organization.');
            $cmd = CommandFactory::getCommand('ShowOrganizationProfile');
            $cmd->setOrganizationId($context->get('organization_id'));
            $cmd->redirect();
        }
        
        $admins = MembershipFactory::getAdminMembershipsByOrganization($context->get('organization_id'), Term::getCurrentTerm());

        $toList = array();
        
        foreach($admins as $admin){
            $toList[] = $admin->getMemberUsername() . '@appstate.edu';
        }
        
        $tags = array();
        
        $tags['ORG_NAME']   = $org->getName(false);
        $tags['FROM_NAME']  = $mbr->getFriendlyName();
        
        $extra = $context->get('extra_message');
        
        if(isset($extra) && !empty($extra)){
            $tags['EXTRA'] = $extra;
            $tags['EXTRA_NAME'] = $mbr->getFirstName();
        }
        
        $email = new EmailMessage('multiple', UserStatus::getUsername(), $toList, UserStatus::getUsername() . '@appstate.edu', UserStatus::getUsername() . '@appstate.edu', NULL, 'Information request: ' . $org->getName(false), 'email/pres/requestInformation.tpl', $tags, FALSE);
        
        $email->send();

        NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS, 'Your request for information has been sent.');
        
    	$successCmd = CommandFactory::getCommand('ShowOrganizationProfile');
    	$successCmd->setOrganizationId($context->get('organization_id'));
    	$successCmd->redirect();
    }
}
