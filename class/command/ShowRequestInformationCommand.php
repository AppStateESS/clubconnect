<?php

PHPWS_Core::initModClass('sdr', 'LockableCommand.php');

class ShowRequestInformationCommand extends LockableCommand {
    
    private $organizationId;
    
    function getRequestVars()
    {
        $vars = array('action' => 'ShowRequestInformation');
        
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
        PHPWS_Core::initModClass('sdr', 'RequestInformationView.php');
        PHPWS_Core::initModClass('sdr', 'Organization.php');

        $org = new Organization($context->get('organization_id'));
        
        $infoView = new RequestInformationView($org);
        
        $context->setContent($infoView->show());
    }
}
