<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationController.php');
PHPWS_Core::initModClass('sdr', 'OrganizationController.php');
PHPWS_Core::initModClass('sdr', 'OfficerRequestController.php');
PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class OrganizationRegistrationSetCommand extends CrudCommand
{
    public function __construct()
    {
        $this->ctrl = new OrganizationRegistrationController();
    }

    public function get(CommandContext $context)
    {
        $regs = $this->ctrl->get();

        $username = UserStatus::getUsername();

        $cmd = CommandFactory::getInstance()->get('ClubRegistrationFormCommand',
            array('registration_id' => null));

        foreach($regs as $reg) {
            $this->ctrl->protect($reg, $username);
            $this->ctrl->addPermissions($reg, $username);

            $cmd->setRegistrationId($reg['registration_id']);
            $reg['url'] = $cmd->getURI();
        }

        $context->setContent($regs);
    }

    public function post(CommandContext $context)
    {
        $pdo = PDOFactory::getInstance();
        $pdo->beginTransaction();

        $reg = $context->getJsonData();
        $reg['committed_by'] = UserStatus::getUsername();

        // Create Organization Record if New
        if(!array_key_exists('organization_id', $reg) || !$reg['organization_id']) {
            $orgCtrl = new OrganizationController();
            $id = $orgCtrl->create(array(
                'banner_id'       => null,
                'locked'          => 0,
                'reason'          => null,
                'rollover_stf'    => 0,
                'rollover_fts'    => 1,
                'student_managed' => 1));

            if(!$id) {
                $pdo->rollBack();
                return;
            }

            $reg['organization_id'] = $id;
        }

        // Create Officer Request Record
        $orCtrl = new OfficerRequestController();
        $id = $orCtrl->create(array(
            'organization_id' => $reg['organization_id'],
            'officers'        => $reg['officers']
        ));

        if(!$id) {
            $pdo->rollBack();
            return;
        }

        $reg['officer_request_id'] = $id;

        // Create Registration
        $id = $this->ctrl->create($reg);

        if($id === FALSE) {
            header('HTTP/1.1 500 Internal Server Error');
            $pdo->rollBack();
        } else {
            $pdo->commit();
            header('HTTP/1.1 201 Created');

            $emails = array(SDRSettings::getApplicationEmail());
            foreach($reg['officers'] as $officer) {
                if($officer['admin']) {
                    $emails[] = $officer['person_email'];
                }
            }

            PHPWS_Core::initModClass('sdr', 'OrganizationApplicationSubmittedEmail.php');
            $email = new OrganizationApplicationSubmittedEmail(
                $emails,
                Term::getPrintableCurrentTerm(),
                $reg['fullname'],
                $_SERVER['HTTP_SHIB_INETORGPERSON_DISPLAYNAME']
            );
            $email->send();

            $cmd = CommandFactory::getInstance()->get(
                'OrganizationRegistrationCommand', array('registration_id' => $id));
            $cmd->get($context);
        }
    }
}

?>
