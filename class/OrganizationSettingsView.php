<?php

/**
 * OrganizationSettingsView - View responsible for creating the interface for allowing admins to change organization settings
 * @author Jeremy Booker
 */

class OrganizationSettingsView extends sdr\View {
    
    private $organization;
    
    public function __construct(Organization $organization)
    {
        $this->organization = $organization;
    }
    
    public function show()
    {
        $org = $this->organization;

        $form = new PHPWS_Form();

        // Disabled
        $form->addCheck('disabled', '1');
        $form->setLabel('disabled','Disabled');

        $form->addText('disabled_reason', $org->getReasonAccessDenied());
        
        $form->setLabel('disabled_reason', 'Reason');
        
        // If disabled, set match on the check box
        if($org->getLocked()){
            $form->setMatch('disabled', '1');
        }

        // Agreement
        $form->addTextarea('agreement', $org->getAgreement());
        $form->setLabel('agreement', 'Agreement');
        
        // Registered
        $form->addCheck('registered', '1');
        $form->setLabel('registered', 'Registered for ' . Term::getPrintableSelectedTerm());

        if($org->registeredForTerm(Term::getSelectedTerm())) {
            $form->setMatch('registered', '1');
        }

        // Change instance particulars
        $form->addText('name', $org->getName(false));
        $form->setLabel('name', 'Name');

        PHPWS_Core::initModClass('sdr', 'OrganizationType.php');
        $types = OrganizationType::getOrganizationTypes();
        $form->addSelect('type',$types);
        $form->setMatch('type', $org->getType());
        $form->setLabel('type', 'Category');

        $form->addCheck('retroactive', '1');
        $form->setLabel('retroactive', 'Apply changes to Name and Type retroactive to prior name/type change');

        $form->addText('address', $org->getAddress());
        $form->setLabel('address', 'Campus Box / Departmental Address');

        $form->addText('bank', $org->getBank());
        $form->setLabel('bank', 'Bank');

        $form->addText('ein', $org->getEin());
        $form->setLabel('ein', 'EIN');

        $form->addHidden('term', Term::getSelectedTerm());
        
        $form->addSubmit('submit', 'Submit changes');
        
        // Setup the command to exec onSubmit
        $submitCmd = CommandFactory::getCommand('OrganizationSettingsCommand');
        $submitCmd->setOrganizationId($org->getId());
        $submitCmd->initForm($form);
        
        $tags = $form->getTemplate();
        $tags['TERM'] = Term::getPrintableSelectedTerm();

        javascript('modules/sdr/OrganizationSettings');
        
        return PHPWS_Template::process($tags, 'sdr', 'OrganizationSettingsView.tpl');
    }
}

?>
