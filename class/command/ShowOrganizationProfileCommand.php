<?php

/**
 * Shows the Organization Profile
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowOrganizationProfileCommand extends Command
{
	protected $organization_id;

    public function __construct($organization_id = null)
    {
        if(!is_null($organization_id)) $this->setOrganizationId($organization_id);
    }

    public function getParams()
    {
        return array('organization_id');
    }

	function setOrganizationId($id)
	{
		$this->organization_id = $id;
	}
	
    function getJavascript()
    {
        return javascript('modules/sdr/OrganizationProfile');
    }

    function execute(CommandContext $context)
    {   
    	if(!isset($this->organization_id)) {
            $this->organization_id = $context->get('organization_id');
    	}
            
        $orgid = $this->organization_id;
        
        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        $orgmanager = new OrganizationManager($orgid);
        
        $context->setContent($orgmanager->showProfile());
        
        $this->setLogOrganization($orgmanager->getOrganization());
    }
}

?>
