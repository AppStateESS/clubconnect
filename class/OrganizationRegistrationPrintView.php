<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OrganizationRegistrationPrintView extends sdr\View
{
    protected $registration;
    protected $offreq;

    public function __construct($registration, $or)
    {
        $this->registration = $registration;
        $this->offreq = $or;
    }

    public function show()
    {
        $reg = $this->registration;
        $or = $this->offreq;

        $reg['elections'] = implode(', ', $reg['elections']);
        $reg['searchtags'] = implode(', ', $reg['searchtags']);

        $sgaelection = array('', 'Greek Life (Fraternities and Sororities Only)', 'Honors / Academics', 'Multicultural Affairs', 'Sports', 'Service Initiative', 'Special Interest');
        if(array_key_exists('sgaelection', $reg) && $reg['sgaelection'] > 0) {
            $reg['sgaelection'] = $sgaelection[$reg['sgaelection']];
        }

        if(array_key_exists('purpose', $reg)) {
            $reg['purpose']      = strip_tags($reg['purpose']);
        }
        if(array_key_exists('description', $reg)) {
            $reg['description']  = strip_tags($reg['description']);
        }
        if(array_key_exists('requirements', $reg)) {
            $reg['requirements'] = strip_tags($reg['requirements']);
        }

        PHPWS_Core::initModClass('sdr', 'Member.php');
        foreach($or['officers'] as &$officer) {
            $role = new Role($officer['role_id']);
            $officer['role'] = $role->getTitle();
            if($officer['admin']) {
                $officer['role'] .= ' (Admin)';
                if($officer['fulfilled']) {
                    $officer['role'] .= "\n  <strong>fulfilled</strong> on " . $officer['fulfilled'];
                } else {
                    $officer['role'] .= "\n  <strong>unfulfilled</strong>";
                }
            }

            $person = new Member(null, $officer['person_email']);
            if($person->getId() > 0) {
                if($person->isAdvisor()) {
                    $number = $person->getAdvisor()->getOfficePhone();
                } else {
                    $addresses = $person->getStudent()->getAddresses('PS');
                    if(!empty($addresses)) {
                        $number = $addresses[0]->getPhone();
                    }
                }

                $officer['contact'] = $person->getFullName() . ' - ' . $number;
            } else {
                $officer['contact'] = 'No Contact Information Available';
            }
        }
        unset($officer);

        $vars = array_merge($reg, $or);

        return PHPWS_Template::process($vars, 'sdr', 'OrganizationRegistrationPrint.tpl');
    }
}

?>
