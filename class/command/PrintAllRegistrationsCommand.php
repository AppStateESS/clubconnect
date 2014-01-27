<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class PrintAllRegistrationsCommand extends CrudCommand
{
    public function allowExecute()
    {
        return UserStatus::isAdmin();
    }

    public function get(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationController.php');
        $regCtrl = new OrganizationRegistrationController();

        PHPWS_Core::initModClass('sdr', 'OfficerRequestController.php');
        $orCtrl = new OfficerRequestController();

        PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationPrintView.php');

        $db = new PHPWS_DB('sdr_organization_registration_view_current');
        $db->addWhere('term', array(201340, 201410));
        $db->addOrder('fullname');
        $db->addColumn('registration_id');
        $db->addColumn('officer_request_id');

        $result = $db->select();

        $contents = array(
            'REGISTRATIONS' => array()
        );

        foreach($result as $r) {
            $reg = $regCtrl->get($r['registration_id']);
            $or = $orCtrl->get($r['officer_request_id']);

            $view = new OrganizationRegistrationPrintView($reg[0], $or[0]);
            $contents['REGISTRATIONS'][]['CONTENTS'] = $view->show();
        }

        $processed = PHPWS_Template::process($contents, 'sdr', 'OrganizationRegistrationPrintAll.tpl');
        Layout::nakedDisplay($processed, 'Club Registration - Print All', true);
    }
}

?>
