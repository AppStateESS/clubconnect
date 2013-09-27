<?php

/**
 * Shows the Rollover Interface
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowRolloverCommand extends Command
{
    public function allowExecute()
    {
        return UserStatus::hasPermission('rollover');
    }

    function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'Rollover.php');

        $r = new Rollover();

        $context->setContent($r->getSettingsView());
    }
}

?>
