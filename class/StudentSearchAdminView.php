<?php

PHPWS_Core::initModClass('sdr', 'StudentSearchView.php');

class StudentSearchAdminView extends StudentSearchView {
    
    function __construct()
    {
        parent::__construct();
    }
    
    function show()
    {
        parent::show();
        
        $terms = array_merge(array(''=>''), Term::getTermsAssoc());
        
        PHPWS_Core::initModClass('sdr', 'Organization.php');
        
        $organizations = Organization::getOrganizationList();
        
        $this->form->addDropBox('organization', $organizations);
        $this->form->setMatch('organization', '0');
        
        $this->form->addDropBox('minTerm', $terms);
        $this->form->addDropBox('maxTerm', $terms);
        
        $tags = $this->form->getTemplate();
        
        Layout::addPageTitle('Search Students and Advisors');
        return PHPWS_Template::process($tags, 'sdr', 'StudentSearchAdminView.tpl');
    }
}
?>
