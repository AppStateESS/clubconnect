<?php

PHPWS_Core::initModClass('sdr', 'AjaxSearchView.php');

/**
 * Handles showing the fancy person viewer with javascript filtering.
 *
 * @author jbooker <jbooker AT tux DOT appstate DOT edu>
 * @package sdr
 */

class FancyPersonBrowser extends AjaxSearchView {
    
    private $personType;
    
    /**
     * Sets the type of person to search for (e.g. 'president', 'advisor').
     * @param string $type the prson type
     */
    public function setPersonType($type){
        $this->personType = $type;
    }
    
    public function show()
    {
        javascript('modules/sdr/PersonBrowser');
        
        $queryCmd = CommandFactory::getCommand('Search');
        
        if(isset($this->personType)){
            $queryCmd->setType($this->personType);
        }
        
        $this->setQueryCommand($queryCmd);
        $this->setSearchWhenEmpty('false');
        $this->setRenderJSCallback('renderPersonResults');
        
        return parent::show();
    }
}

?>