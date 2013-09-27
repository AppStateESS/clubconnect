<?php

  /**
   * UI to rename an organization
   *
   * @author Robert Bost <bostrt at appstate dot edu>
   */

PHPWS_Core::initModClass('sdr', 'Organization.php');
PHPWS_Core::initModClass('sdr', 'OrganizationType.php');

class RenameOrganizationView extends sdr\View
{

    private $parent;

    public function __construct(Organization $org)
    {
        if(is_a($org, 'Organization')){
            $this->parent = $org;
        } else {
            return;
        }
    }

    public function show()
    {
        if(UserStatus::isAdmin()){
            javascript('modules/sdr/RenameOrganization');

            $form = new PHPWS_Form();

            $submit_cmd = CommandFactory::getCommand('RenameOrganization');
            $submit_cmd->initForm($form);

            $form->addText('org_name');
            $form->addDropBox('org_type', OrganizationType::getOrganizationTypes());
            $form->addHidden('parent_id', $this->parent->getId());
            $form->addCheck('register', 'register');
            $form->addCheck('preserve', 'preserve');
            $form->setMatch('register', 'register');
            $form->setMatch('preserve', 'preserve');

            $form->setLabel('org_name', 'Name: ');
            $form->setLabel('org_type', 'Category: ');
            $form->setLabel('register', 'Register: ');
            $form->setLabel('preserve', 'Preserve Memberships: ');

            $form->addSubmit('submit', 'Rename Organization');

            $tpl = $form->getTemplate();
        
            Layout::addPageTitle('Rename Organization');
        
            return PHPWS_Template::process($tpl, 'sdr', 'RenameOrganization.tpl');
        }
    }

}

?>
