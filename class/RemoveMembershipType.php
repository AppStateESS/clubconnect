<?php

// From the Organization's point of view
define('REMOVE_ORG_REMOVE', 1);     // Remove Existing Membership
define('REMOVE_ORG_CANCEL', 2);     // Cancel Membership Request
define('REMOVE_ORG_DECLINE', 3);    // Decline Membership Request

// From the User's point of view
define('REMOVE_USER_REMOVE', 4);     // Remove Existing Membership
define('REMOVE_USER_CANCEL', 5);     // Cancel Membership Request
define('REMOVE_USER_DECLINE', 6);    // Decline Membership Request

class RemoveMembershipType
{
	private $type;
	
	public function __construct(Membership $membership)
	{
        $level = $membership->getLevel();
        if($membership->getMember()->getUsername() == UserStatus::getUsername()) {
            // Student is removing.
            
            // Awaiting student confirmation
            // Student is declining membership request
            if($level == MBR_LEVEL_AWAITING_STUDENT) {
                $this->type = REMOVE_USER_DECLINE;
                
            // Awaiting organization confirmation
            // Student is cancelling their request for membership
            } else if($level == MBR_LEVEL_AWAITING_ORG) {
                $this->type = REMOVE_USER_CANCEL;
            
            // Membership has been established
            // Student is withdrawing their membership
            } else {
                $this->type = REMOVE_USER_REMOVE;
            }
        } else {
            // Organization is removing.
            
            // Awaiting student confirmation
            // Organization is cancelling request for the student to join
            if($level == MBR_LEVEL_AWAITING_STUDENT) {
                $this->type = REMOVE_ORG_CANCEL;
                
            // Awaiting organization confirmation
            // Organization is declining the student's request
            } else if($level == MBR_LEVEL_AWAITING_ORG) {
                $this->type = REMOVE_ORG_DECLINE;
            
            // Membership has been established
            // Organization is removing the student from membership
            } else {
                $this->type = REMOVE_ORG_REMOVE;
            }
        }
	}
	
	public function getType()
	{
		return $type;
	}
    
    public function getQuestion()
    {
        $string = 'Error in RemoveMembershipType::getQuestion';
        
        switch($this->type) {
            case REMOVE_ORG_REMOVE:
                $string = 'Are you sure you want to remove this membership record?';
                break;
            case REMOVE_ORG_CANCEL:
                $string = 'Are you sure you want to cancel this membership request?';
                break;
            case REMOVE_ORG_DECLINE:
                $string = 'Are you sure you want to decline this request for membership?';
                break;
            case REMOVE_USER_REMOVE:
                $string = 'Are you sure you want to remove your membership in this organization?';
                break;
            case REMOVE_USER_CANCEL:
                $string = 'Are you sure you want to cancel your request to join this organization?';
                break;
            case REMOVE_USER_DECLINE:
                $string = 'Are you sure you want to decline this organization\'s request for you to join?';
                break;
        }
        
        return dgettext('sdr', $string);
    }
    
    public function isOrg()
    {
    	return $this->type == REMOVE_ORG_REMOVE ||
    	       $this->type == REMOVE_ORG_CANCEL ||
    	       $this->type == REMOVE_ORG_DECLINE;
    }
    
    public function isStudent()
    {
    	return !$this->isOrg();
    }
    
    public function getSubmit()
    {
        switch($this->type) {
            case REMOVE_ORG_REMOVE:
            case REMOVE_USER_REMOVE:
                return dgettext('sdr', 'Remove Membership');
            case REMOVE_ORG_CANCEL:
            case REMOVE_USER_CANCEL:
                return dgettext('sdr', 'Cancel Membership Request');
            case REMOVE_ORG_DECLINE:
            case REMOVE_USER_DECLINE:
                return dgettext('sdr', 'Decline Membership Request');
        }
    }
    
    public function getEmailSubject()
    {
    	switch($this->type) {
    		case REMOVE_ORG_REMOVE:
    		case REMOVE_USER_REMOVE:
    			return dgettext('sdr', 'Club Membership Removal Notification');
    		case REMOVE_ORG_CANCEL:
    		case REMOVE_USER_CANCEL:
    			return dgettext('sdr', 'Club Membership Request Cancellation');
    		case REMOVE_ORG_DECLINE:
    		case REMOVE_USER_DECLINE:
    			return dgettext('sdr', 'Club Membership Request Declined');
    	}
    }
    
    public function getEmailTemplate()
    {
    	switch($this->type) {
    		case REMOVE_ORG_REMOVE:
    			return 'email/student/removeMembershipNotification.tpl';
    		case REMOVE_ORG_CANCEL:
    			return 'email/student/cancelMembershipNotification.tpl';
    		case REMOVE_ORG_DECLINE:
    			return 'email/student/declineMembershipNotification.tpl';
    		case REMOVE_USER_REMOVE:
                return 'email/pres/removeMembershipNotification.tpl';
    		case REMOVE_USER_CANCEL:
                return 'email/pres/cancelMembershipNotification.tpl';
    		case REMOVE_USER_DECLINE:
                return 'email/pres/declineMembershipNotification.tpl';
    	}
    }
    
    public function sendEmail($membership, $reason = NULL)
    {
    	PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
    	$username = $membership->getMember()->getUsername();
    	$email = new EmailMessage(
    	    $username,
    	    'sdr_system',
    	    $username . '@appstate.edu',
    	    NULL, NULL, NULL,
    	    $this->getEmailSubject(),
    	    $this->getEmailTemplate());
    	
    	$email_tags = array();
    	$email_tags['NAME'] = $membership->getMember()->getFriendlyName();
    	$email_tags['ORG_NAME'] = $membership->getOrganizationName(false);
    	if(!is_null($reason)) {
    	   $email_tags['REASON'] = $reason;
    	}
        if(!Term::isCurrentTermSelected()) {
            $email_tags['TERM'] = Term::getPrintableSelectedTerm();
        }
    	
    	$email->setTags($email_tags);
    	$email->send();
    }

    public function notify($membership)
    {
        $name = $membership->getMember()->getFullName();
        $org = $membership->getOrganizationName();
        $term = Term::toString($membership->getTerm());

        switch($this->type) {
            case REMOVE_ORG_REMOVE:
                $message = sprintf(dgettext('sdr', 'Removed %s from %s for %s.'),
                    $name, $org, $term);
                break;
            case REMOVE_ORG_CANCEL:
                $message = sprintf(dgettext('sdr', 'Cancelled request for %s to join %s for %s.'),
                    $name, $org, $term);
                break;
            case REMOVE_ORG_DECLINE:
                $message = sprintf(dgettext('sdr', 'Declined request from %s to join %s for %s.'),
                    $name, $org, $term);
                break;
            case REMOVE_USER_REMOVE:
                $message = sprintf(dgettext('sdr', 'You have removed yourself from %s.'),
                    $org);
                break;
            case REMOVE_USER_CANCEL:
                $message = sprintf(dgettext('sdr', 'You have cancelled your request to join %s.'),
                    $org);
                break;
            case REMOVE_USER_DECLINE:
                $message = sprintf(dgettext('sdr', 'You have declined a request to join %s.'),
                    $org);
                break;
        }

        NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS, $message);
    }
}
