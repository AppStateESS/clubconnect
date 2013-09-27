<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
PHPWS_Core::initModClass('sdr', 'Member.php');

class DenyOrganizationApplicationEmail
{
    protected $emails;
    protected $term;
    protected $name;
    protected $user;

    public function __construct(array $emails, $term, $name, $user)
    {
        $this->emails = $emails;
        $this->term   = $term;
        $this->name   = $name;
        $this->user   = $user;
    }

    public function send()
    {
        $term = $this->term;
        $name = $this->name;
        $subject = "$term Club Registration Update: $name";

        $email = new EmailMessage(NULL, NULL,
            $emails,
            NULL,
            NULL,
            NULL,
            $subject,
            'DenyOrganizationApplicationEmail.tpl');

        $tpl = array();
        $tpl['ORG_NAME'] = $this->name;
        $tpl['DENYING_USER'] = $this->user;

        $email->setTags($tpl);
        $email->send();
    }
}

?>
