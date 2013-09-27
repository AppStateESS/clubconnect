<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');

class AdminApproveOrganizationApplicationEmail
{
    protected $emails;
    protected $name;
    protected $term;
    protected $href;

    public function __construct(array $emails, $term, $name, $href)
    {
        $this->emails       = $emails;
        $this->term         = $term;
        $this->name         = $name;
        $this->href         = $href;
    }

    public function send()
    {
        $term = Term::toString($this->term);
        $name = $this->name;
        $subject = "$term Club Registration: $name";

        $email = new EmailMessage(NULL, NULL,
            $this->emails,
            NULL,
            NULL,
            NULL,
            $subject,
            'AdminApproveOrganizationApplicationEmail.tpl');

        $tpl = array();
        $tpl['ORG_NAME'] = $this->name;
        $tpl['APP_LINK'] = $this->href;
        
        $email->setTags($tpl);
        $email->send();
    }
}

?>
