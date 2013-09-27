<?php

/**
 * Edit Roles View (for admins)
 * 
 * Show roles that administrator can show/hide.
 * Or they can create a new role.
 *
 * @author Robert Bost <bostrt at appstate dot edu>
 */

class EditRolesView extends sdr\View
{

  public function show()
  {
    if(UserStatus::isAdmin()){

      javascript('modules/sdr/CreateRole');

      $pager = $this->listRoles();
      $form = $this->newRoleForm();

      Layout::addPageTitle('Edit Roles');

      return $pager->get().$form;
    }
  }

  private function listRoles()
  {
      PHPWS_Core::initCoreClass('DBPager.php');
      PHPWS_Core::initModClass('sdr', 'Role.php');
    
      $pager = new DBPager('sdr_role', 'Role');
      $pager->setModule('sdr');
      $pager->setLink('index.php?module=sdr');
      $pager->setEmptyMessage('No roles found');
      $pager->setTemplate('EditRoles.tpl');
      $pager->addRowTags('getRowTags');
      // Add some sortage
      $pager->addSortHeader('title', 'Title');
      $pager->addSortHeader('rank', 'Rank');
      $pager->addSortHeader('hidden', 'Hidden');
      $pager->setDefaultOrder('title');
      $pager->setDefaultLimit(10000);

      return $pager;
  }

  private function newRoleForm()
  {
    $cmd = CommandFactory::getCommand('EditRolesCommand');
    $form = new PHPWS_Form('new_role_form');
    $cmd->initForm($form);

    $form->addButton('show_form', 'Create New Role');
    $form->addText('role_title');
    $form->setLabel('role_title', 'Role title ');
    $ranks = array('-1'=>'Pre-Member ("Pledge")','0'=>'Member','1'=>'Low-level Officer',
		   '2'=>'Medium-level Officer','3'=>'High-level Officer','10'=>'Advisor');
    $form->addSelect('role_rank',$ranks);
    $form->setLabel('role_rank', 'Rank ');
    $visibility = array('0'=>'Visible', '1'=>'Hidden');
    $form->addSelect('visibility',$visibility);
    $form->setLabel('visibility', 'Visibility ');
    $form->addSubmit('submit', 'Submit new role');

    $tpl = $form->getTemplate();

    return PHPWS_Template::process($tpl, 'sdr', 'NewRole.tpl');
  }
}
?>
