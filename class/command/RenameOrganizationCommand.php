<?php

  /**
   * Create a child organization and set the parent org's child field.
   * Used when a club officially changes names or types.
   *
   * @author Robert Bost <bostrt at appstate dot edu>
   */

PHPWS_Core::initModClass('sdr', 'Command.php');

class RenameOrganizationCommand extends Command 
{

    function getRequestVars()
    {
        return array('action' => 'RenameOrganization');
    }

    /**
     * @param $context contains parentId, org name, org type, and how to handle memberships
     */
    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()){
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to rename organizations.');
        }
     
        PHPWS_Core::initModClass('sdr', 'Organization.php');

        $parentId    = $context->get('parent_id');
        $org_name    = $context->get('org_name');
        $org_type    = $context->get('org_type');
        $preserve    = $context->get('preserve');
        $register    = $context->get('register');
        $error_cmd   = CommandFactory::getCommand('ShowRenameOrganization');

        // Check that options are set
        if(!isset($parentId) || empty($parentId)){
            $adminBrowser = CommandFactory::getCommand('ShowAdminOrganizationBrowser');
            $adminBrowser->redirect();
        }
        if(!isset($org_name)  || empty($org_name)){
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Please enter a Name');
            $error_cmd->setOrganizationId($parentId);
            $error_cmd->redirect();
        }
        if(!isset($org_type) || empty($org_type)){
            $error_cmd->redirect();
        }

        // Initialize parent and child org
        $child = new Organization();
        $child->setName($org_name);
        $child->setType($org_type);
        $parent = new Organization($parentId);

        if($parent->id < 0) {
            PHPWS_Core::initModClass('sdr', 'exception/OrganizationNotFoundException.php');
            throw new OrganizationNotFoundException('Organization does not exist.', $parent);
        }

        if(!is_null($parent->getChild())){
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('Cannot rename organization that has already been renamed.');
        }

        $result = $child->save();
        $currTerm = Term::getCurrentTerm();
        
        // Update parent's child field
        $this->setParentsChild($parentId, $child->getId(), $currTerm);
        // A parent cannot register or be registered
        PHPWS_Core::initModClass('sdr', 'Term.php');
        $parent->unregisterForTerm($currTerm);
        
        // New child org can register on the fly
        if($register == 'register'){
            $child->registerForTerm($currTerm);
        }
        if($preserve == 'preserve'){
            $this->moveMembers($parentId, $child->getId(), $currTerm);
        }

        $success_cmd = CommandFactory::getCommand('ShowOrganizationRoster');
        $success_cmd->setOrganizationId($child->getId());
        $success_cmd->redirect();
    }

    /**
     * Set the parent organization's child field to the child org's ID
     */
    private function setParentsChild($parentId, $childId)
    {
        $db = new PHPWS_DB('sdr_organization');
        
        $db->addWhere('id', $parentId);
        $db->addValue('child', $childId);
        $db->update();
    }
    
    private function moveMembers($parentId, $childId, $currTerm)
    {
        $db = new PHPWS_DB('sdr_membership');
        
        $db->addWhere('organization_id', $parentId);
        $db->addWhere('term', $currTerm);
        $db->addValue('organization_id', $childId);

        $db->update();
    }
}
?>
