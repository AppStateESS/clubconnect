<?php

PHPWS_Core::initModClass('sdr', 'AjaxSearchView.php');

/**
 * Handles showing the fancy organization viewer with javascript filtering.
 *
 * @author jbooker <jbooker AT tux DOT appstate DOT edu>
 * @package sdr
 */

class FancyOrganizationBrowser extends AjaxSearchView {

    private $orgType; // The org type to search for (e.g. 'registered', 'unregistered')

    /**
     * Sets the type of organization to search for (e.g. 'registered', 'unregistered').
     * @param string $type the organization type
     */
    public function setOrgType($type){
        $this->orgType = $type;
    }
    
    public function show()
    {
        javascript('modules/sdr/OrganizationBrowser');

        $queryCmd = CommandFactory::getCommand('OrganizationSearch');
        
        if(isset($this->orgType)){
            $queryCmd->setType($this->orgType);
        }
        
        $this->setQueryCommand($queryCmd);
        $this->setSearchWhenEmpty('true');
        
        $this->setRenderJSCallback('renderResults');
        
        return parent::show();
    }
    
}

?>