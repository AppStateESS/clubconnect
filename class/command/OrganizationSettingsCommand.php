<?php

/**
 * Shows the Organization Settings
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class OrganizationSettingsCommand extends CrudCommand
{
    protected $organization_id;

    public function getParams()
    {
        return array('organization_id');
    }
    
    public function setOrganizationId($id)
    {
        $this->organization_id = $id;
    }
    
    public function get(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view the settings of this organization.');
        }

        if(!isset($this->organization_id)) {
        	$this->organization_id = $context->get('organization_id');
        }
        
        $orgid = $this->organization_id;

        PHPWS_Core::initModClass('sdr', 'Organization.php');
        $org = new Organization($orgid, Term::getSelectedTerm());

        PHPWS_Core::initModClass('sdr', 'OrganizationView.php');
        $view = new OrganizationView($org);

        PHPWS_Core::initModClass('sdr', 'OrganizationSettingsView.php');
        $settingsview = new OrganizationSettingsView($org);

        $view->setMain($settingsview->show());
        
        $context->setContent($view->show());
    }

    public function post(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to change the settings of this organization.');
        }
        
        // Command for showing the settings again on success/failure
        PHPWS_Core::initModClass('sdr', 'Organization.php');
        $org = new Organization($this->organization_id);

        // Save the Permanent Settings
        if($context->get('disabled')){
            $org->setLocked(true);
            $org->setReasonAccessDenied($context->get('disabled_reason'));
        }else{
            $org->setLocked(false);
            $org->setReasonAccessDenied(NULL);
        }

        $org->setAgreement($context->get('agreement'));

        $org->save();
        NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS, 'Permanent Settings Saved');

        // Handle the registration situation
        PHPWS_Core::initModClass('sdr', 'OrganizationInstance.php');

        if(!$context->get('registered')) {
            if($org->registeredForTerm(Term::getSelectedTerm())) {
                // Organization is registered, we're unregistering.
                PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
                $memberships = MembershipFactory::getMembershipsByOrganization($org->getId(), Term::getSelectedTerm());
                if(count($memberships) > 0) {
                    // If the roster isn't empty, we're not going to unregister it.
                    NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Cannot unregister an organization for a term in which there are memberships.  If you are trying to deny access to this organization, please use the "disabled" feature.');
                    $this->redirect();
                }
                $instance = $org->getInstance(Term::getSelectedTerm());
                $instance->delete();
                NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS, $org->getName() . ' was unregistered for ' . Term::getPrintableSelectedTerm() . '.');
            }

            $this->redirect();
        } else {
            if($org->registeredForTerm(Term::getSelectedTerm())) {
                $instance = $org->getInstance(Term::getSelectedTerm());
            } else {
                $instance = new OrganizationInstance();
                $instance->setOrganizationId($org->getId());
                $instance->setTerm(Term::getSelectedTerm());
                NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS, $org->getName() . ' was registered for ' . Term::getPrintableSelectedTerm() . '.');
            }
        }

        // Update Term settings
        if($context->get('retroactive')) {
            $db = new PHPWS_DB('sdr_organization_instance');
            $db->addWhere('organization_id', $org->id);
            $db->addWhere('term', $context->get('term'), '<=');
            $db->addWhere('name', $org->getName(false));
            $db->addWhere('type', $org->getType());
            $db->addValue('name', $context->get('name'));
            $db->addValue('type', $context->get('type'));
            SDR::throwDb($db->update());
            NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS, 'New name and type applied retroactively.');
        } 

        $instance->setName($context->get('name'));
        $instance->setType($context->get('type'));
        $instance->setAddress($context->get('address'));
        $instance->setBank($context->get('bank'));
        $instance->setEin($context->get('ein'));

        $instance->save();
        NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS, 'Saved Term settings for ' . Term::getPrintableSelectedTerm() . '.');
        
        $this->redirect();
    }
}
