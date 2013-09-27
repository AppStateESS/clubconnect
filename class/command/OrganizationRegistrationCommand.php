<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationController.php');
PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class OrganizationRegistrationCommand extends CrudCommand
{
    protected $registration_id;

    public function __construct()
    {
        $this->ctrl = new OrganizationRegistrationController();
    }

    public function getParams()
    {
        return array('registration_id');
    }

    public function setRegistrationId($id)
    {
        $this->registration_id = $id;
    }

    public function get(CommandContext $context)
    {
        if($this->registration_id == 'new') {
            $context->setContent(array(
                'parent'             => null,
                'searchtags'         => array(),
                'elections'          => array(),
                'allowView'          => true,
                'allowModify'        => true,
                'allowState'         => true,
                'state'              => 'New',
                'officer_request_id' => 'new'
            ));
            return;
        }

        $reg = $this->ctrl->get($this->registration_id);
        if(!$reg) {
            header('HTTP/1.1 404 Not Found');
            return;
        }

        $reg = $reg[0];
        $username = UserStatus::getUsername();

        $this->ctrl->protect($reg, $username);
        $this->ctrl->addPermissions($reg, $username);

        $cmd = CommandFactory::getInstance()->get('ClubRegistrationFormCommand',
            array('registration_id' => $reg['registration_id']));
        $reg['url'] = $cmd->getURI();

        $context->setContent($reg);
    }

    public function post(CommandContext $context)
    {
        $pdo = PDOFactory::getInstance();
        $pdo->beginTransaction();

        $reg = $context->getJsonData();

        $reg['committed_by'] = UserStatus::getUsername();

        $id = $this->ctrl->save($reg);

        if($id === FALSE) {
            header('HTTP/1.1 500 Internal Server Error');
            $pdo->rollBack();
        } else {
            $pdo->commit();


            // Send Emails
            if($reg['state'] == 'Approved') {
                PHPWS_Core::initModClass('sdr', 'OfficerRequestController.php');
                $orc = new OfficerRequestController();
                $offreq = $orc->get($reg['officer_request_id']);
                $offreq = $offreq[0];

                $emails = array(SDRSettings::getApplicationEmail());
                foreach($offreq['officers'] as $officer) {
                    if($officer['admin']) {
                        $emails[] = $officer['person_email'] . '@appstate.edu';
                    }
                }

                $cmd = CommandFactory::getInstance()->get('OfficerRequestAgreementCommand',
                    array('offreq_id' => $reg['officer_request_id']));
                $href = $cmd->getURI();

                PHPWS_Core::initModClass('sdr', 'AdminApproveOrganizationApplicationEmail.php');
                $email = new AdminApproveOrganizationApplicationEmail(
                    $emails,
                    $reg['term'],
                    $reg['fullname'],
                    'https://sdr.appstate.edu' . $href
                );
                $email->send();
            }

            header('HTTP/1.1 200 OK');

            $this->get($context);
        }
    }
}

?>
