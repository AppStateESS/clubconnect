<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
PHPWS_Core::initModClass('sdr', 'Member.php');

class OrganizationApplicationSubmittedEmail
{
    protected $emails;
    protected $term;
    protected $name;

    public function __construct(array $emails, $term, $name, $submitter)
    {
        $this->emails    = $emails;
        $this->term      = $term;
        $this->name      = $name;
        $this->submitter = $submitter;
    }

    public function send()
    {
        $term = $this->term;
        $name = $this->name;
        $subject = "$term Club Registration Submitted: $name";

        $email = new EmailMessage(NULL, NULL,
            $this->emails,
            NULL,
            NULL,
            NULL,
            $subject,
            'OrganizationApplicationSubmittedEmail.tpl');

        $tpl = array();
        $tpl['ORG_NAME'] = $this->name;
        $tpl['APPROVING_USER'] = $this->submitter;

        $email->setTags($tpl);
        $email->send();
    }
}

?>
