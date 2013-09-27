<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class SaveOrganizationApplicationCommand extends Command
{
    function getRequestVars()
    {
        return array('action' => 'SaveOrganizationApplication');
    }

    function execute(CommandContext $context)
    {
    }
}

?>
