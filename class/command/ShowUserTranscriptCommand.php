<?php

/**
 * Shows the User Transcript
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowUserTranscriptCommand extends Command
{
    protected $member_id;

    public function getParams()
    {
        return array('member_id');
    }

    public function setMemberId($id)
    {
        $this->member_id = $id;
    }
	
	public function execute(CommandContext $context)
	{
        if(!isset($this->member_id)) {
            $this->member_id = $context->get('memberId');
        }

	if(UserStatus::isGuest()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('Please log in to view your transcript.');
        }

        $memberId = $this->member_id;

	    PHPWS_Core::initModClass('sdr', 'Member.php');
	    PHPWS_Core::initModClass('sdr', 'Transcript.php');
	    PHPWS_Core::initModClass('sdr', 'TranscriptBrowserView.php');
	    
        // If a member ID is set and user is admin, show a specified
        // transcript; otherwise just show the user's, ignoring any
        // bad data
		$student = !is_null($memberId) && UserStatus::isAdmin() ?
            new Member($memberId) :
            new Member(NULL, UserStatus::getUsername());
        // TODO: It's a permission exception if they try to set a username that's not their own, period.

		$transcript = new Transcript($student);
		
		$transcriptVew = new TranscriptBrowserView($transcript);

		$context->setContent($transcriptVew->show());
	}
}
