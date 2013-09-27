<?php
/**
 * Hide Role Command.
 *
 * Make a selected role hidden. 
 * Changes the hidden column in sdr_role table to 1.
 *
 * @author Robert Bost <bostrt at appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'RoleCommand.php');

class HideRoleCommand extends RoleCommand
{
    protected $role_id;

    public function getParams()
    {
        return array('role_id');
    }

    public function setRoleId($id)
    {
        $this->role_id = $id;
    }

    public function execute(CommandContext $context)
    {
        $db = new PHPWS_DB('sdr_role');
        $db->addWhere('id', $this->role_id);
        $db->addValue('hidden', 1);
        $db->update();

        $cmd = CommandFactory::getCommand('EditRoles');
        $cmd->redirect();
    }
}

?>
