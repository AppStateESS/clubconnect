<?php

/**
 * SDR Organization Browser Pager View
 * Provides a DB-Paged fallback to the Category view for legacy browsers
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OrganizationBrowserSimpleView extends sdr\View
{
    var $viewCommand;
    var $organizations;

    public function __construct(array $orgs)
    {
        $this->organizations = $orgs;
    }

    /**
     * Call this method only if control is to be returned to the server when
     * an organization is successfully selected in jQuery land.
     *
     * @param $vars array An array of arrays, that looks like this:
     *      array(
     *          array('PARAM'=>'Parameter1', 'VALUE'=>'Value1'),
     *          array('PARAM'=>'Parameter2', 'VALUE'=>'Value2'), ... );
     */
    public function setViewCommand(Command $cmd)
    {
        $this->viewCommand = $cmd;
    }

    public function show()
    {
        $tpl = new PHPWS_Template('sdr');
        $result = $tpl->setFile('OrganizationBrowserSorted.tpl');
        if(PHPWS_Error::logIfError($result)) {
            return "Template Error in OrganizationBrowserSorted Rendering.";
        }
        
        // Link for adding a new organization
        if(UserStatus::isAdmin()) {
            $cmd = CommandFactory::getCommand('ShowCreateOrganization');
            $tpl->setData(array('NEW_ORG_LINK'=>$cmd->getLink('Create a New Organization')));
        }

        $lastcat = 'NOCAT';
        foreach($this->organizations as $org) {
            $registered = '';
            $tpl->setCurrentBlock('LIST');
            $data = array();
            
            if($org['category'] != $lastcat) {
            	$data['CAT_NAME'] = $org['category'];
            	
                $lastcat = $org['category'];
            }

            $linked = $org['name'];
            if(isset($this->viewCommand)) {
            	$cmd = $this->viewCommand;
            	$cmd->setOrganizationId($org['id']);
                $linked = $cmd->getLink($linked);
            	$linked = PHPWS_Text::moduleLink($linked, 'sdr',
            	    $cmd->getRequestVars());
            }
            
            $data['ORG_NAME'] = $linked;
            if(is_null($org['term'])) {
            	$data['REGISTERED'] = dgettext('sdr', '(Not Registered)');
            }

            $tpl->setData($data);
            $tpl->parseCurrentBlock();
        }

        if(isset($this->requestVars)) {
            // TODO: Link to the Organization Profiles
//            test($this->callbackVars);
        }

        Layout::addPageTitle('Browse Organizations');
        return $tpl->get();
    }
}

?>
