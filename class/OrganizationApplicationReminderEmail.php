<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');

class OrganizationApplicationReminderEmail
{
    private $app;
    private $recipients;

    public function __construct(OrganizationApplication $app)
    {
        $this->app = $app;
        $this->recipients = array();

        if(!$this->app->pres_confirmed)
            $this->recipients[] = $this->app->_req_pres->getUsername() . '@appstate.edu';

        if(!$this->app->advisor_confirmed)
            $this->recipients[] = $this->app->_req_advisor->getUsername() . '@appstate.edu';
    }

    public function send()
    {
        $term = Term::toString($this->app->term);
        $name = $this->app->name;
        $subject = "$term Club Registration REMINDER: $name";

        $to = $this->recipients;
        $to[] =SDRSettings::getApplicationEmail();

        $email = new EmailMessage(NULL, NULL, $to, NULL, NULL, NULL, $subject,
            'OrganizationApplicationReminderEmail.tpl');

        $tpl = array();
        $tpl['ORG_NAME'] = $name;
        $cmd = CommandFactory::getCommand('ViewOrganizationApplication');
        $cmd->setApplicationId($this->app->id);
        $tpl['APP_LINK'] = 'https://sdr.appstate.edu' . $cmd->getUri();

        $email->setTags($tpl);
        $email->send();
    }

    public function getRecipients()
    {
        return $this->recipients;
    }
}

?>
