<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowHelpCommand extends Command
{
    public function execute(CommandContext $context)
    {
    }

    public function getLink($text = NULL, $target = NULL, $cssClass = NULL, $title = NULL)
    {
        if(is_null($text)) $text = 'HELP: Tutorial Videos';
        return '<a href="/help">' . $text . '</a>';
    }
}
