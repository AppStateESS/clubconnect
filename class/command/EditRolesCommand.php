<?php

  /**
   * Show Edit Roles menu for Admins
   *
   * @author Robert Bost <bostrt at appstate dot edu>
   */


PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class EditRolesCommand extends CrudCommand
{
    public function allowExecute()
    {
        return UserStatus::hasPermission('role_admin');
    }

    public function get(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'EditRolesView.php');
        $editRoles = new EditRolesView();
        $context->setContent($editRoles->show());
    }

    public function post(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'Role.php');
        $title = $context->get('role_title');
        $title = trim($title);

        if(!empty($title)){
            if(!Role::roleExistsByTitle($title)){
                $role = new Role();
                $role->setTitle($context->get('role_title'));
                $role->setRank($context->get('role_rank'));
                $role->setHidden(false);
            } else {
                NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'The role '.$title.' already exists.');
                CommandContext::goBack();
            }
        } else {
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Please enter a Role Title.');
            CommandContext::goBack();
        }

        $role->save();

        $this->redirect();
    }
}


?>
