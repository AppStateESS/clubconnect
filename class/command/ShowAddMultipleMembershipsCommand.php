<?php

/**
 * Command class which handles creating the view to show the interface for adding multiple members at one time
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'LockableCommand.php');

class ShowAddMultipleMembershipsCommand extends LockableCommand
{
    public $organizationId;
    
    public function setOrganizationId($id){
        $this->organizationId = $id;
    }
    
    function getRequestVars()
    {
        return array('action' => 'ShowAddMultipleMemberships', 'organization_id'=>$this->organizationId);
    }
    
    function execute(CommandContext $context)
    {
      PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
      if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
	PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
	throw new PermissionException(
				      dgettext('sdr', GlobalLock::persistentMessage()));
      }
        PHPWS_Core::initModClass('sdr', 'AddMultipleMembershipsView.php');
        PHPWS_Core::initModClass('sdr', 'Organization.php');
        
        $org = new Organization($context->get('organization_id'));
        $view = new AddMultipleMembershipsView($org);
        
        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        $manager = new OrganizationManager($org);
        $manager->ifLocked('You may not add memberships because ');
        
        $context->setContent($view->show());
    }
}
?>
