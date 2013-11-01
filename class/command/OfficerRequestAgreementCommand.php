<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'OfficerRequestController.php');
PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class OfficerRequestAgreementCommand extends CrudCommand
{
    protected $offreq_id;

    public function __construct()
    {
        $this->ctrl = new OfficerRequestController();
    }

    public function getParams()
    {
        return array('offreq_id');
    }

    public function setOfficerRequestId($id)
    {
        $this->offreq_id = $id;
    }

    public function allowExecute()
    {
        return UserStatus::isUser();
    }

    public function get(CommandContext $context)
    {
        $offreq = $this->ctrl->get($this->offreq_id);
        if(!$offreq || count($offreq) == 0) {
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Could not load specified officer request.');
            $context->errorBack();
        }
        $offreq = $offreq[0];
        $offdata = null;

        $username = UserStatus::getUsername();
        foreach($offreq['officers'] as $officer) {
            if($officer['person_email'] == $username) {
                $offdata = $officer;
            }
        }

        if(is_null($offdata)) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You are not listed on this officer request.');
        }

        PHPWS_Core::initModClass('sdr', 'Role.php');
        $role = new Role($offdata['role_id']);

        PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationController.php');
        $rctrl = new OrganizationRegistrationController();
        $reg = $rctrl->get(null, null, null, $offreq['officer_request_id']);
        $reg = $reg[0];

        if($offdata['role_id'] == 53) {
            $agreements = array();
        } else {
            $agreements = array(
                array('CONTENT' => SDRSettings::getAdministratorAgreement())
            );
            PHPWS_Core::initModClass('sdr', 'Organization.php');
            $org = new Organization($offreq['organization_id']);
            $agreement = $org->getAgreement();
            if($agreement) {
                $agreements[] = array('CONTENT' => $agreement);
            }
        }
            
        $vars = array(
            'FULLNAME'   => $reg['fullname'],
            'TERM'       => Term::getPrintableCurrentTerm(),
            'ROLE'       => $role->getTitle(),
            'AGREEMENTS' => $agreements,
            'FORMURI'    => $this->getURI(),
            'OFFREQ_ID'  => $offreq['officer_request_id']
        );

        if($offdata['fulfilled']) {
            // User has already fulfilled an officer request
            $context->setContent(PHPWS_Template::process($vars, 'sdr', 'OfficerRequestCompleted.tpl'));
        } else {
            if($reg['state'] == 'Rejected') {
                // Registration has been rejected
                $context->setContent(PHPWS_Template::process($vars, 'sdr', 'OfficerRequestRejected.tpl'));
            } else if($reg['state'] != 'Approved') {
                // Registration has not been approved
                $context->setContent(PHPWS_Template::process($vars, 'sdr', 'OfficerRequestNotReady.tpl'));
            } else {
                // User has not yet fulfilled an officer request
                $context->setContent(PHPWS_Template::process($vars, 'sdr', 'OfficerRequest.tpl'));
            }
        }
    }

    public function post(CommandContext $context)
    {
        // If Reject, set RegistrationForm to OfficerRejected state
        // If Accept, mark time, then
        //      Email Other Officers
        //      If all other officers have accepted, register club

        $pdo = PDOFactory::getInstance();
        $pdo->beginTransaction();

        if(!is_null($context->get('reject'))) {
            PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationController.php');
            $rctrl = new OrganizationRegistrationController();

            list($reg) = $rctrl->get(null, null, null, $this->offreq_id);
            $reg['state'] = 'Rejected';
            $reg['statecomment'] = 'Rejected by ' . $_SERVER['HTTP_SHIB_INETORGPERSON_DISPLAYNAME'];
            $reg['committed_by'] = UserStatus::getUsername();

            $rctrl->saveState($reg);

            $pdo->commit();
            
            list($req) = $this->ctrl->get($this->offreq_id);

            $emails = array(SDRSettings::getApplicationEmail());
            foreach($req['officers'] as $officer) {
                if($officer['admin']) {
                    $emails[] = $officer['person_email'] . '@appstate.edu';
                }
            }

            PHPWS_Core::initModClass('sdr', 'DenyOrganizationApplicationEmail.php');
            $email = new DenyOrganizationApplicationEmail(
                $emails,
                Term::toString($reg['term']),
                $reg['fullname'],
                $_SERVER['HTTP_SHIB_INETORGPERSON_DISPLAYNAME']
            );
            $email->send();
        } else if(!is_null($context->get('accept'))) {
            // Mark Approved
            $this->ctrl->fulfill($this->offreq_id, UserStatus::getUsername());

            PHPWS_Core::initModClass('sdr', 'RoleController.php');
            $rc = new RoleController();
            $certRoles = $rc->getRequiredForCertification();

            // See if all admin requests have been fulfilled
            list($request) = $this->ctrl->get($this->offreq_id);
            $fulfilled = array();
            $pending = array();
            foreach($request['officers'] as $officer) {
                if(!in_array($officer['role_id'], $certRoles)) continue;

                if(is_null($officer['fulfilled'])) {
                    $pending[] = $officer;
                } else {
                    $fulfilled[] = $officer;
                }
            }

            PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationController.php');
            $rctrl = new OrganizationRegistrationController();
            list($reg) = $rctrl->get(null, null, null, $this->offreq_id);

            if(empty($pending)) {
                $reg['state'] = 'Certified';
                $reg['statecomment'] = null;
                $reg['committed_by'] = UserStatus::getUsername();

                $rctrl->saveState($reg);
            }

            $pdo->commit();

            // Send Emails
            $emails = array(SDRSettings::getApplicationEmail());
            foreach($request['officers'] as $officer) {
                if(!$officer['admin']) continue;

                $emails[] = $officer['person_email'] . '@appstate.edu';
            }

            $term = Term::toString($reg['term']);
            $name = $reg['fullname'];
            $user = $_SERVER['HTTP_SHIB_INETORGPERSON_DISPLAYNAME'];

            PHPWS_Core::initModClass('sdr', 'UserApproveOrganizationApplicationEmail.php');
            $email = new UserApproveOrganizationApplicationEmail(
                $emails, $term, $name, $user);
            $email->send();
        }

        $this->redirect();
    }
}

?>
