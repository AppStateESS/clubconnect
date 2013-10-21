<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');
PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationController.php');
PHPWS_Core::initModClass('sdr', 'OfficerRequestController.php');
PHPWS_Core::initModClass('sdr', 'process/RegistrationCertified.php');

class CertifyRegistrationsCommand extends Command
{
    public function allowExecute()
    {
        return UserStatus::getUsername() == 'ticklejw';
    }

    public function execute(CommandContext $context)
    {
        $regCtrl = new OrganizationRegistrationController();
        $orCtrl  = new OfficerRequestController();
        $process = new RegistrationCertified();

        $regs = $regCtrl->get(null, null, 201340, null);

        $status = array();

        foreach($regs as $reg) {
            $status[] = 'Processing ' . $reg['registration_id'] . ' (' . $reg['fullname'] . ') with state' .$reg['state'];
            if($reg['state'] != 'Approved') {
                $status[] = 'Not in state Approved, skipping...';
            }
            list($req) = $orCtrl->get($reg['officer_request_id']);
            foreach($req['officers'] as $officer) {
                if(in_array($officer['role_id'], array(53,4,6,52,15,18,20,21,34,44))) {
                    if(is_null($officer['fulfilled'])) {
                        $status[] = $officer['person_email'] . 'has not fulfilled, skipping';
                        continue 2;
                    }
                    if(!is_numeric($officer['member_id'])) {
                        $status[] = 'Found garbaled Member ID ' . $officer['member_id'] . ', skipping';
                        continue 2;
                    }
                }
            }

            $process->execute($reg);            
        }

        var_dump($status);
    }
}

?>
