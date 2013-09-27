<?php

/**
 * ViewTranscriptRequestCommand - Shows a transcript request, can be used in jQuery popup
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ViewTranscriptRequestCommand extends Command {
    private $transcriptRequestId;

    function getRequestVars()
    {
        $vars = array('action' => 'ViewTranscriptRequest');

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
	//	test($req->getDeliveryMethod(),1);

        PHPWS_Core::initModClass('sdr', 'TranscriptRequestProcessView.php');
        $view = new TranscriptRequestProcessView($req);

        $context->setContent($view->show());
    }

}

?>
