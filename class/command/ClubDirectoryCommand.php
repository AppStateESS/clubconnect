<?php

PHPWS_Core::initModClass('sdr', 'AngularViewCommand.php');

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class ClubDirectoryCommand extends AngularViewCommand
{
    public function getRawFile()
    {
        if(UserStatus::isAdmin()) {
            return 'AdminClubDirectory.html';
        }

        return 'ClubDirectory.html';
    }
}

?>
