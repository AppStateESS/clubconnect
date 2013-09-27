<?php

/**
 * RequestInformationView - Shows the view for requesting more information
 */

class RequestInformationView extends sdr\View {
    
    private $organization;
    
    function __construct(Organization $org){
        $this->organization = $org;
    }
    
    public function show()
    {
        $form = new PHPWS_Form('request_info_form');
        $tpl = array();
        
        $backCmd = CommandFactory::getCommand('ShowOrganizationProfile');
        $backCmd->setOrganizationId($this->organization->getId());
        $tpl['CANCEL'] = $backCmd->getLink('Cancel');
        
        $submitCmd = CommandFactory::getCommand('RequestInformation');
        $submitCmd->setOrganizationId($this->organization->getId());
        
        $submitCmd->initForm($form);
        
        $form->addTextArea('extra_message');
        $form->addSubmit('submit', 'Submit request');
        
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        return PHPWS_Template::process($tpl, 'sdr', 'RequestInformationView.tpl');
    }
}
?>
