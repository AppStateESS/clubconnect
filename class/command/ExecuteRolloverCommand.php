<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ExecuteRolloverCommand extends Command {

    function getRequestVars()
    {
        $vars = array('action', 'ExecuteRollover');

        return $vars;
    }
    
    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('What do you think I am, a n00b?');
        }

        PHPWS_Core::initModClass('sdr', 'Rollover.php');
        $r = new Rollover();
        $r->execute();

        \sdr\Environment::getInstance()->cleanExit();
    }
}

?>
