<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class OrganizationRegistrationPrintCommand extends CrudCommand
{
    protected $registration_id;

    public function allowExecute()
    {
        return !UserStatus::isGuest();
    }

    public function getParams()
    {
        return array('registration_id');
    }

    public function get(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationController.php');
        $regCtrl = new OrganizationRegistrationController();

        PHPWS_Core::initModClass('sdr', 'OfficerRequestController.php');
        $orCtrl = new OfficerRequestController();

        $reg = $regCtrl->get($this->registration_id);
        if(empty($reg)) {
            throw new Exception('Could not load registration with ID ' . $this->registration_id);
            return;
        }
        $reg = $reg[0];

        $or = $orCtrl->get($reg['officer_request_id']);
        if(!$or) {
            throw new Exception('Could not load officer request with ID ' .
                $reg['officer_request_id'] . ' referenced from registration with ID ' .
                $reg['id']);
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationPrintView.php');
        $view = new OrganizationRegistrationPrintView($reg, $or);

        Layout::nakedDisplay($view->show(), 'Club Registration - ' . $reg['fullname'], true);
    }
}

?>
