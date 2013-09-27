<?php
/**
 * RequestMembershipCommand
 *
 * Adds a membership request to a particular organization.
 *
 * @author     Daniel West <dwest at tux dot appstate dot edu>
 * @package    mod
 * @subpackage sdr
 */
PHPWS_Core::initModClass('sdr', 'LockableCommand.php');

class RequestMembershipCommand extends LockableCommand {

    private $organizationId;

    function getRequestVars()
    {
        $vars = array('action' => 'RequestMembership');
         
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
      // If Global Lock is enabled then user can't request membership
      	PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
	if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
	  PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
	  throw new PermissionException(
					dgettext('sdr', GlobalLock::persistentMessage()));
	}

        $orgid = $context->get('organization_id');
        	
        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        PHPWS_Core::initModClass('sdr', 'Member.php');

        $cmd = CommandFactory::getCommand('ShowOrganizationProfile');
        $cmd->setOrganizationId($orgid);

	// Check permissions and user status
        if(UserStatus::isGuest()) {
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'You must log in using the link below before you can request membership in this organization.');
            $cmd->redirect();
        }

        $mbr    = new Member(NULL, UserStatus::getUsername());
        $orgMgr = new OrganizationManager(new Organization($orgid));
        
        $orgMgr->ifLocked('You may not request membership in this organization because ');

        try {
            $membership = $orgMgr->requestMembership($mbr);
            NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS, 'You have requested membership in this organization.');
        } catch(UnregisteredOrganizationException $uoe) {
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'You can not request membership in an unregistered organization.');
        } catch(CreateMembershipException $e){
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'There was an error while processing your membership request: ' . $e->getMessage());
        }
        
        //TODO send the admin an email

        $cmd->redirect();
    }
}
?>
