<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class SummaryApplicationsView extends sdr\View
{
    var $applications;

    public function __construct(array $applications)
    {
        $this->applications = $applications;
    }

    public function show()
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationApplicationMenu.php');
        
        $tpl = array();
        PHPWS_Core::initModClass('sdr', 'NoApplicationsMenu.php');
        $menu = new NoApplicationsMenu();
        $tpl['APPLICATIONS_MENU'] = $menu->show();
        if(empty($this->applications)) {
            $tpl['NO_APPLICATIONS_MESSAGE'] = 'You have no pending club registrations.';
        } else foreach($this->applications as $a) {
            $menu = new OrganizationApplicationMenu($a);

            $awaiting = '';
            if(!$a->admin_confirmed) {
                $awaiting = ' (Awaiting CSIL Approval)';
            } else {
                $awaiting = ' (Pending since ' . date('m/d/Y', $a->created_on) . ')';
            }

            $atpl = array();
            $atpl['REGISTRATION'] = $a->name;
            $atpl['SINCE']        = $awaiting;
            $atpl['ACTIONS']      = $menu->show();
            $tpl['REGISTRATIONS'][] = $atpl;
        }

        return PHPWS_Template::process($tpl, 'sdr', 'SummaryApplicationsView.tpl');
    }
}

?>
