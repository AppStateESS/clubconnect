<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');

class NoApplicationsMenu extends CommandMenu
{
    protected function setupCommands()
    {
        $this->addCommandByName('Register an Organization', 'ClubRegistrationFormCommand');
    }
}

?>
