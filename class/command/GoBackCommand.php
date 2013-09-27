<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class GoBackCommand extends Command
{
    public function execute(CommandContext $context)
    {
        $context->goBack();
    }
}
