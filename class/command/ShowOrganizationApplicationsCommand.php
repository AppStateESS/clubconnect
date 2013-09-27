<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'AngularViewCommand.php');

class ShowOrganizationApplicationsCommand extends AngularViewCommand
{
    public function allowExecute()
    {
        return UserStatus::isAdmin() && UserStatus::hasPermission('registration_admin');
    }

    public function getRawFile()
    {
        return 'RegistrationList.html';
    }
}

?>
