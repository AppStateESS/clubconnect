<?php

class CreateOrganizationView extends sdr\View
{
    public $name;
    public $type;
    public $address;
    public $bank;
    public $ein;
    public $managed;

    public $submitCommand;

    public function setSubmitCommand(Command $cmd)
    {
        $this->submitCommand = $cmd;
    }

    public function show(){
        PHPWS_Core::initModClass('sdr', 'OrganizationType.php');

        $form = new PHPWS_Form('new_organization');
        $this->submitCommand->initForm($form);
        
        $form->addText('name', $this->name);
        $form->setLabel('name', 'Organization Name');
        $form->setRequired('name');

        $form->addDropBox('type',OrganizationType::getOrganizationTypes());
        $form->setMatch('type', $this->type);
        $form->setLabel('type', 'Category');
        $form->setRequired('type');

        $form->addText('address', $this->address);
        $form->setLabel('address', 'ASU Box / Departmental Address');

        $form->addText('bank', $this->bank);
        $form->setLabel('bank', 'Bank');

        $form->addText('ein', $this->ein);
        $form->setLabel('ein', 'EIN');

        $form->addCheck('student_managed', 1);
        $form->setLabel('student_managed', 'Students will manage this organization');
        $form->setMatch('student_managed', 1);
        
        $form->addSubmit('submit', 'Create organization');
        
        $tpl = $form->getTemplate();
        
        Layout::addPageTitle('Create Organization');
        return PHPWS_Template::process($tpl, 'sdr', 'CreateOrganizationView.tpl');
    }
}


?>
