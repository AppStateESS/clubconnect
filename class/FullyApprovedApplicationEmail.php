<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
PHPWS_Core::initModClass('sdr', 'Organization.php');

class FullyApprovedApplicationEmail
{
    protected $term;
    protected $name;
    protected $emails;
    protected $orgid;

    public function __construct(array $emails, $term, $name, $orgid)
    {
        $this->emails = $emails;
        $this->term   = $term;
        $this->name   = $name;
        $this->orgid  = $orgid;
    }

    public function send()
    {
        $term = $this->term;
        $name = $this->name;
        $subject = "$term Club Registration Complete: $name";

        $email = new EmailMessage(NULL, NULL,
            $emails,
            NULL,
            NULL,
            NULL,
            $subject,
            'FullyApprovedApplicationEmail.tpl');

        $tpl = array();
        $tpl['ORG_NAME'] = $this->name;

        $roster = CommandFactory::getCommand('ShowOrganizationRoster');
        $roster->setOrganizationId($this->orgid);
        $tpl['ROSTER_LINK'] = 'https://clubconnect.appstate.edu'.$roster->getUri();

        $view = CommandFactory::getCommand('ShowOrganizationProfile');
        $view->setOrganizationId($this->orgid);
        $tpl['VIEW_PROFILE_LINK'] = 'https://clubconnect.appstate.edu'.$view->getUri();

        $edit = CommandFactory::getCommand('EditOrganizationProfile');
        $edit->setOrganizationId($this->orgid);
        $tpl['EDIT_PROFILE_LINK'] = 'https://clubconnect.appstate.edu'.$edit->getUri();

        $email->setTags($tpl);
        $email->send();
    }
}

?>

