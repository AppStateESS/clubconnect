<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');

class AdminDeleteOrganizationApplicationEmail
{
    private $app;

    public function __construct(OrganizationApplication $app)
    {
        $this->app = $app;
    }

    public function send()
    {
        $term = Term::toString($this->app->term);
        $name = $this->app->name;
        $subject = "$term Club Registration Deleted: $name";

        if(!is_null($this->app->_req_advisor)) {
            $advisor = $this->app->_req_advisor->getUsername();
        } else {
            $advisor = $this->app->req_advisor_email;
        }
        $email = new EmailMessage(NULL, NULL,
            array($this->app->_req_pres->getUsername() . '@appstate.edu',
                  $advisor . '@appstate.edu',
                  SDRSettings::getApplicationEmail()),
            NULL,
            NULL,
            NULL,
            $subject,
            'AdminDeleteOrganizationApplicationEmail.tpl');

        $tpl = array();
        $tpl['ORG_NAME'] = $this->app->name;

        $email->setTags($tpl);
        $email->send();
    }
}

?>
