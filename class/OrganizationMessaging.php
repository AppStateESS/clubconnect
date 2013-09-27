<?php

PHPWS_Core::initModClass('sdr', 'Organization.php');

class OrganizationMessaging
{
    protected $organization;
    protected $submitCommand;

    public function __construct($organization)
    {
        if(is_a($organization, 'Organization')) {
            $this->organization = $organization;
        } else {
            $this->organization = new Organization($organization);
        }

        $this->submitCommand = CommandFactory::getCommand('OrganizationMessagingCommand');
        $this->submitCommand->setOrganizationId($this->organization->getId());
    }

    public function show()
    {
        $form = new PHPWS_Form();

        $this->submitCommand->initForm($form);

        $form->addText('mail_subject');
        $form->setLabel('mail_subject', 'Subject');
        $form->setSize('mail_subject', 40);

        $form->addTextArea('mail_body');
        $form->setLabel('mail_body', 'Message');
        $form->useEditor('mail_body', true, true, 0, 0, 'fckeditor');

        $form->addSubmit('submit', 'Send');

        $tpl = $form->getTemplate();

        $tpl['MAIL_TO_LABEL'] = "To:";
        $tpl['MAIL_TO'] = "All Members of " . $this->organization->getName(false);

        return PHPWS_Template::process($tpl, 'sdr', 'MessageComposeForm/form.tpl');
    }
}
