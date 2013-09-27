<?php

PHPWS_Core::initModClass('sdr', 'AngularViewCommand.php');
PHPWS_Core::initModClass('sdr', 'PDOFactory.php');

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class ProcessRegistrationsCommand extends AngularViewCommand
{
    public function allowExecute()
    {
        return UserStatus::isAdmin();
    }

    public function getRawFile()
    {
        return 'ProcessRegistrations.html';
    }
}

?>
