<?php

PHPWS_Core::initModClass('sdr', 'command/Command.php');

class UnmarkTranscriptRequestProcessedCommand extends Command
{
	private $transcriptRequestId;
	
    function getRequestVars()
    {
    	$vars = array('action' => 'UnmarkTranscriptRequestProcessed');
    	
    	if(isset($this->transcriptRequestId)) {
    		$vars['tr_id'] = $this->transcriptRequestId;
    	}
    	
    	return $vars;
    }
    
    function setTranscriptRequestId($id)
    {
        $this->transcriptRequestId = $id;
    }
    
    function execute(CommandContext $context)
    {
    	if(!isset($this->transcriptRequestId)) {
    		$this->transcriptRequestId = $context->get('tr_id');
    	}

        PHPWS_Core::initModClass('sdr', 'TranscriptRequest.php');
        $req = new TranscriptRequest($this->transcriptRequestId);

        $req->setProcessed(0);
        $req->save();

        $cmd = CommandFactory::getCommand('ShowTranscriptRequests');
        $cmd->redirect();
    }
}
