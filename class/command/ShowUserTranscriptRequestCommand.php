<?php

/**
 * Command class for showing the official transcript request form
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowUserTranscriptRequestCommand extends Command {
    
    function getRequestVars()
    {
        $vars = array('action'=>'ShowUserTranscriptRequest');
        
        return $vars;
    }
    
    function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'TranscriptRequestCreateView.php');
        
        PHPWS_Core::initModClass('sdr', 'Member.php');
        $student = new Member(NULL, UserStatus::getUsername());
        $reqView = new TranscriptRequestCreateView($student);
                
        $context->setContent($reqView->show());
    }
}

?>
