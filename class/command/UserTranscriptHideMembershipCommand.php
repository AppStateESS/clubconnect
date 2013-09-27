<?php

/**
 * Command class which hides a membership on the student transcript
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'LockableCommand.php');

class UserTranscriptHideMembershipCommand extends LockableCommand {
    
    private $membershipId;
    private $membershipType;
    
    function setMembershipId($id){
        $this->membershipId = $id;
    }
    
    function setMembershipType($type){
        $this->membershipType = $type;
    }
    
    function getRequestVars()
    {
        $vars = array('action' => 'UserTranscriptHideMembership', 'membership_id' => $this->membershipId, 'membership_type' => $this->membershipType);
        
        return $vars;
    }
    
    function execute(CommandContext $context)
    {
      /** If Global Lock is on user cannot do anything that will alter DB **/
      PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
      if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
	PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
	throw new PermissionException(
				      dgettext('sdr', GlobalLock::persistentMessage()));
      }
        PHPWS_Core::initModClass('sdr', 'Membership.php');
        PHPWS_Core::initModClass('sdr', 'SDR_Academics.php');
        PHPWS_Core::initModClass('sdr', 'Student_Employment.php');
        
        PHPWS_Core::initModClass('sdr', 'Transcript.php');
        
        $this->membershipId     = $context->get('membership_id');
        $this->membershipType   = $context->get('membership_type');
        
        if($this->membershipType == MBR_TYPE_CLUB){
            $membership = new Membership($this->membershipId);
            $membership->setHidden(true);
            $membership->save();
        }else if($this->membershipType == MBR_TYPE_DC_LIST){
            SDR_Deans_Chancellors::setVisible($this->membershipId, false);
        }else if($this->membershipType == MBR_TYPE_SCHOLARSHIP){
            SDR_Scholarships::setVisible($this->membershipId, false);
        }else if($this->membershipType == MBR_TYPE_EMPLOYMENT){
            Student_Employment::setVisible($this->membershipId, false);
        }
        
        $cmd = CommandFactory::getCommand('ShowUserTranscript');
        $cmd->redirect();
    }
}

?>
