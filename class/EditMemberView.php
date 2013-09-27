<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class EditMemberView extends sdr\View
{
    private $member;
    private $saveCommand;

    public function __construct(Member $member, Command $command)
    {
        $this->member = $member;
        $this->saveCommand = $command;
    }

    public function show() {
        $form = new PHPWS_Form('new_member');
        $this->saveCommand->initForm($form);

        $member = $this->member;

        $form->addText('username', $member->getUsername());
        $form->setLabel('username', 'AppalNet Username');
        $form->setRequired('username');

        $form->addText('prefix', $member->getPrefix());
        $form->setLabel('prefix', 'Prefix');

        $form->addText('first_name', $member->getFirstName());
        $form->setLabel('first_name', 'First');
        $form->setRequired('first_name');

        $form->addText('middle_name', $member->getMiddleName());
        $form->setLabel('middle_name', 'Middle');

        $form->addText('last_name', $member->getLastName());
        $form->setLabel('last_name', 'Last');
        $form->setRequired('last_name');

        $form->addText('suffix', $member->getSuffix());
        $form->setLabel('suffix', 'Suffix');

        if($member->isAdvisor()) $advisor = $member->getAdvisor();
        else $advisor = new Advisor();

        $form->addText('home_phone', $advisor->getHomePhone());
        $form->setLabel('home_phone', 'Home Phone');

        $form->addText('office_phone', $advisor->getOfficePhone());
        $form->setLabel('office_phone', 'Office Phone');

        $form->addText('cell_phone', $advisor->getCellPhone());
        $form->setLabel('cell_phone', 'Cell Phone');

        $form->addText('office_location', $advisor->getOfficeLocation());
        $form->setLabel('office_location', 'Office Location');

        $form->addText('department', $advisor->getDepartment());
        $form->setLabel('department', 'Department');

        $form->addSubmit('Save Record');

        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'sdr', 'EditMemberView.tpl');
    }
}

?>
