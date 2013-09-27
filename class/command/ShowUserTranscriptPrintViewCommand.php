<?php

/**
 * Shows the User Transcript
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowUserTranscriptPrintViewCommand extends Command
{
    private $memberId;

    function getRequestVars()
    {
        $vars = array('action' => 'ShowUserTranscriptPrintView');

        if(isset($this->memberId)) {
            $vars['memberId'] = $this->memberId;
        }

        return $vars;
    }
    
    function getLink($text = NULL, $target = NULL, $cssClass = NULL, $title = NULL)
    {
        $address = 'index.php?module=sdr';
        foreach(self::getRequestVars() as $key=>$var){
            $address .= "&$key=$var";
        }
        return Layout::getJavascript('open_window', array(
            'label'=>dgettext('sdr', $text),
            'address'=>$address,
            'width'=>600,
            'height'=>800));
    }

    function setMemberId($id)
    {
        $this->memberId = $id;
    }
    
    function execute(CommandContext $context)
    {
        if(!isset($this->memberId)) {
            $this->memberId = $context->get('memberId');
        }

        $memberId = $this->memberId;
        
        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'Transcript.php');
        PHPWS_Core::initModClass('sdr', 'TranscriptPrintView.php');

        // If a member ID is set and user is admin, show a specified
        // transcript; otherwise just show the user's, ignoring any
        // bad data
		$student = !is_null($memberId) && UserStatus::isAdmin() ?
            new Member($memberId) :
            new Member(NULL, UserStatus::getUsername());

        $transcript = new Transcript($student);
        
        $transcriptView = new TranscriptPrintView($transcript);
        
        Layout::nakedDisplay($transcriptView->show());
    }
}

?>
