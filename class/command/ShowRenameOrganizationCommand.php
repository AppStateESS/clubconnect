<?php

  /**
   * Show the view to rename an organization.
   * @author Robert Bost <bostrt at appstate dot edu>
   */


PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowRenameOrganizationCommand extends Command
{
    private $organization_id;

    public function setOrganizationId($id)
    {
        $this->organization_id = $id;
    }

    public function getRequestVars()
    {
        $vars = array('action' => 'ShowRenameOrganization');

        if(isset($this->organization_id)){
            $vars['organization_id'] = $this->organization_id;
        }
        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()){
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to rename organizations.');
        }
        if(!isset($this->organization_id)){
            $this->organization_id = $context->get('organization_id');
        }
        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        $orgmanager = new OrganizationManager($this->organization_id);
        
        $context->setContent($orgmanager->showRename());
    }
}
?>
