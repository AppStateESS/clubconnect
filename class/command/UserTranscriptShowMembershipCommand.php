<?php

/**
 * Command class which shows a membership on the student transcript
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class UserTranscriptShowMembershipCommand extends Command{
    
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
        $vars = array('action' => 'UserTranscriptShowMembership', 'membership_id' => $this->membershipId, 'membership_type' => $this->membershipType);
        
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
            $membership->setHidden(false);
            $membership->save();
        }else if($this->membershipType == MBR_TYPE_DC_LIST){
            SDR_Deans_Chancellors::setVisible($this->membershipId, true);
        }else if($this->membershipType == MBR_TYPE_SCHOLARSHIP){
            SDR_Scholarships::setVisible($this->membershipId, true);
        }else if($this->membershipType == MBR_TYPE_EMPLOYMENT){
            Student_Employment::setVisible($this->membershipId, true);
        }
        
        $cmd = CommandFactory::getCommand('ShowUserTranscript');
        $cmd->redirect();
    }
}
?>
