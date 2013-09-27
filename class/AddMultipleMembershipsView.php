<?php

/**
 * Add Multiple Memberships View
 * Handles showing the form/interface for adding multiple
 * members to an organization at one time
 * @author Jeremy Booker
 */

class AddMultipleMembershipsView extends \sdr\View {
    
    private $org;
    private $users; // array of user names/banner IDs to insert into the list by default
    
    function __construct($org, Array $users = NULL){
        $this->org = $org;
        $this->users = $users;
    }
    
    public function show(){
        
        $tags = array();
        $tags['ORG_NAME'] = $this->org->getName();
        
        $submitCmd = CommandFactory::getCommand('AddMultipleMemberships');
        $submitCmd->setOrganizationId($this->org->getId());
        
        $form = new PHPWS_Form();
        $submitCmd->initForm($form);
        
        if(isset($this->users) && !is_null($this->users) && $this->users!= "")
        {
            $form->addTextArea('members',!is_null($this->users)?implode("\n", $this->users):'');
        }else{
            $form->addTextArea('members');
        }
        $form->addSubmit('submit', 'Add Members');
        
        $form->mergeTemplate($tags);
        $tags = $form->getTemplate();
        
        return PHPWS_Template::process($tags, 'sdr', 'AddMultipleMembershipsView.tpl');
    }
}
