<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');

class NoApplicationsMenu extends CommandMenu
{
    public function __construct()
    {
        parent::__construct();

        $this->addCommandByName('Register an Organization', 'ClubRegistrationFormCommand');
    }
}

?>
