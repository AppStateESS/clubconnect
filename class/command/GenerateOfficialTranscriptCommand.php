<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class GenerateOfficialTranscriptCommand extends Command
{
	private $memberId;
	
    function getRequestVars()
    {
    	$vars = array('action' => 'GenerateOfficialTranscript');
    	
    	if(isset($this->memberId)) {
    		$vars['member_id'] = $this->memberId;
    	}
    	
    	return $vars;
    }
    
    function setMemberId($id)
    {
    	$this->memberId = $id;
    }
    
    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to generate an official transcript.');
        }

    	if(!isset($this->memberId)) {
    		$this->memberId = $context->get('member_id');
    	}
    	
        $memberId = $this->memberId;

        $random = substr(sha1(rand().microtime()), 0, 4);

        $downloadFilename = "sdr_transcript_{$memberId}_{$random}.pdf";

        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'Transcript.php');
        PHPWS_Core::initModClass('sdr', 'TranscriptPDFGenerator.php');

        $m = new Member($memberId);
        if(!$m->isStudent()) {
            NQ::Simple('sdr', SDR_NOTIFICATION_WARNING, 'The specified student does not have a student record!');
            $context->goBack();
        }
        
        $transcript = new Transcript(new Member($memberId));
        $transcriptGenerator = new TranscriptPDFGenerator($transcript);
        $filename = $transcriptGenerator->show();

        if(!file_exists($filename)) {
            PHPWS_Core::initModClass('sdr', 'exception/PDFGeneratorException.php');
            throw new PDFGeneratorException('An error occurred generating an official transcript.');
        }

        header('Content-type: application/force-download');
        header('Content-Transfer-Encoding: Binary');
        header('Content-length: ' . filesize($filename));
        header('Content-disposition: attachment; filename="'.$downloadFilename.'"');
        readfile($filename);
        unlink($filename);
        exit();
    }
}
