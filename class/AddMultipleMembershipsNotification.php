<?php

PHPWS_Core::initModClass('notification', 'SimpleNotification.php');

class AddMultipleMemberhipsNotification extends SimpleNotification {
    
    protected $exceptions;
    
    function __construct($type, $content, Array $exceptions){
        parent::__construct($type, $content);
        
        //$this->exceptions = $exceptions;
    }
    
    function toString()
    {
        $tpl = array();
        $tpl['MSG'] = $this->content;
        
        foreach($this->exceptions as $e){
            $tpl['USERS'][] = array('NAME'=>$e->getUserId(), 'REASON'=>$e->getMessage());
        }
        
        return PHPWS_Template::process($tpl, 'sdr', 'AddMultipleMembershipsExceptionNotification.tpl');
    }
}

?>