<?php

PHPWS_Core::initModClass('sdr', 'AngularViewCommand.php');

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PeopleCommand extends AngularViewCommand
{
    public function getRawFile()
    {
        return 'People.html';
    }
}

?>
