<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');

class BrowseOrganizationsMenu extends CommandMenu
{
    protected function setupCommands()
    {
        $browse = CommandFactory::getCommand('ClubDirectory');
        $this->addCommand('Club Directory', $browse);

        $create = CommandFactory::getCommand('CreateOrganization');
        $this->addCommand('Create New', $create);

        $apply = CommandFactory::getCommand('ClubRegistrationFormCommand');
        $this->addCommand('Register an Organization', $apply);
    }
}

?>
