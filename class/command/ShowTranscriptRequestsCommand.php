<?php

/**
 * ShowTranscriptRequestsCommand - Shows transcript requests to an admin for processing
 * @author Jeff Tickle
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowTranscriptRequestsCommand extends Command
{
    public function allowExecute()
    {
        return UserStatus::hasPermission('transcript_admin');
    }
    
    public function execute(CommandContext $context)
    {
        $viewCommand = CommandFactory::getCommand('ViewTranscriptRequest');

    	PHPWS_Core::initModClass('sdr', 'ProcessTranscriptsView.php');
    	$view = new ProcessTranscriptsView($viewCommand);
    	$context->setContent($view->show());
    }
}
