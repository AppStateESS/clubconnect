<?php

abstract class RoleCommand extends Command
{
    protected $roleId;

    public function allowExecute()
    {
        return UserStatus::hasPermission('role_admin');
    }

    public function setRoleId($id)
    {
        $this->roleId = $id;
    }

}


?>
