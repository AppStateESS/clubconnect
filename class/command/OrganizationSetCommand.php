<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'OrganizationController.php');
PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class OrganizationSetCommand extends CrudCommand
{
    public function __construct()
    {
        $this->ctrl = new OrganizationController();
    }

    public function get(CommandContext $context)
    {
    }
}

?>
