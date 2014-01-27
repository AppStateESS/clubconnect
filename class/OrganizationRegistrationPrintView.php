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
        $reg['sgaelection'] = $sgaelection[$reg['sgaelection']];

        $reg['purpose']      = strip_tags($reg['purpose']);
        $reg['description']  = strip_tags($reg['description']);
        $reg['requirements'] = strip_tags($reg['requirements']);

        foreach($or[0]['officers'] as &$officer) {
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
        }
        unset($officer);

        $vars = array_merge($reg, $or[0]);

        return PHPWS_Template::process($vars, 'sdr', 'OrganizationRegistrationPrint.tpl');
    }
}

?>
