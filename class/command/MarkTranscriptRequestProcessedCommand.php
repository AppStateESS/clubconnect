<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class MarkTranscriptRequestProcessedCommand extends Command
{
	private $transcriptRequestId;
	
    function getRequestVars()
    {
    	$vars = array('action' => 'MarkTranscriptRequestProcessed');
    	
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

        $req->setProcessed(1);
        $req->save();

        //gets information to send student email
        $memb_id = $req->getMemberId();
        PHPWS_Core::initModClass('sdr', 'Member.php');
        $member = new Member($memb_id);
        $name = $member->getFullName();
        $email = $req->getEmail();

        //sends the email
        PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
        $s_email = new EmailMessage($email,'sdr_system', $email, NULL, NULL, NULL, 'Official Transcript Request','email/student/transcriptProcessedNotification.tpl');
        $email_tags = $req->getTags();
        $email_tags['NAME'] = $name;

        switch($req->getDeliveryMethod()) {
            case CSIL_OFFICE:
                $email_tags['MESSAGE'] = 'Your Transcript Request has been processed and is available for pickup at the CSIL office.';
                break;
            case ASU_BOX:
                $email_tags['MESSAGE'] = 'Your Transcript Request has been processed and will be delivered to your ASU PO Box within the next few days.';
                break;
            default:
                $email_tags['MESSAGE'] = 'Your Transcript Request has been processed and mailed to the selected address.';
                break;
        }

        $s_email->setTags($email_tags);
        $s_email->send();

        $cmd = CommandFactory::getCommand('ShowTranscriptRequests');
        $cmd->redirect();
    }
}
