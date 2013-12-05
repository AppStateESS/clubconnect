<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');
PHPWS_Core::initModClass('sdr', 'TranscriptRequest.php');

class TranscriptRequestProcessMenu extends CommandMenu
{
    protected function setupCommands()
    {
        $generate = CommandFactory::getCommand('GenerateOfficialTranscript');
        $generate->setMemberId($req->getMemberId());
        $this->addCommand('Generate Official PDF', $generate);

        if($req->getProcessed()) {
            $unprocess = CommandFactory::getCommand('UnmarkTranscriptRequestProcessed');
            $unprocess->setTranscriptRequestId($req->getId());
            $this->addCommand('Mark Unprocessed', $unprocess);
        } else {
            $process = CommandFactory::getCommand('MarkTranscriptRequestProcessed');
            $process->setTranscriptRequestId($req->getId());
            $this->addCommand('Mark Processed', $process);
        }
    }
}

?>
