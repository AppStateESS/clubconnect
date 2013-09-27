<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
PHPWS_Core::initModClass('sdr', 'Member.php');

class UserApproveOrganizationApplicationEmail
{
    protected $emails;
    protected $name;
    protected $term;
    protected $user;

    public function __construct(array $emails, $term, $name, $user)
    {
        $this->emails = $emails;
        $this->name   = $name;
        $this->term   = $term;
        $this->user   = $user;
    }

    public function send()
    {
        $term = $this->term;
        $name = $this->name;
        $subject = "$term Club Registration Update: $name";

        $email = new EmailMessage(NULL, NULL,
            $this->emails,
            NULL,
            NULL,
            NULL,
            $subject,
            'UserApproveOrganizationApplicationEmail.tpl');

        $tpl = array();
        $tpl['ORG_NAME'] = $name;
        $tpl['APPROVING_USER'] = $this->user;

        $email->setTags($tpl);
        $email->send();
    }
}

?>
