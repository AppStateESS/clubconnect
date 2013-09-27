<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class DebugCommand extends CrudCommand
{
    public function get(CommandContext $context)
    {
        var_dump($_SESSION['clubreg-debug']);
    }
}

?>
