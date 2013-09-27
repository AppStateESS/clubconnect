<?php

PHPWS_Core::initModClass('sdr', 'Organization.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');

class OrganizationHistory
{
    protected $organization;

    public function __construct($organization)
    {
        if(is_a($organization, 'Organization')) {
            $this->organization = $organization;
        } else {
            $this->organization = new Organization($organization);
        }
    }

    public function show()
    {
        $db = new PHPWS_DB('sdr_organization_application');
        $db->addWhere('organization_id', $this->organization->getId());
        $db->addOrder('term');

        $result = $db->select();

        $ret = '<h2>Registration Forms:</h2><ul>';
        foreach($result as $r) {
            $cmd = CommandFactory::getCommand('ViewOrganizationApplication');
            $cmd->setApplicationId($r['id']);
            $ret .= '<li>' . $cmd->getLink(Term::toString($r['term'])) . '</li>';
        }
        $ret .= '</ul>';

        return $ret;
    }
}
