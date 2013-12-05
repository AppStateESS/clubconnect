<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');

class BrowseMembersMenu extends CommandMenu
{
    protected function setupCommands()
    {
        if(UserStatus::isAdmin()) {
            $search = CommandFactory::getCommand('PeopleCommand');
            $this->addCommand('Search', $search);
            $create = CommandFactory::getCommand('ShowCreateMember');
            $this->addCommand('Create New', $create);
        }
    }
}

?>
