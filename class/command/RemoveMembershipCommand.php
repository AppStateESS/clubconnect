<?php

/**
 * Removes the membership with the given id
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');
PHPWS_Core::initModClass('sdr', 'RemoveMembershipType.php');

class RemoveMembershipCommand extends Command {
    
    private $membershipId;
    private $reason;
    
    private $onSuccessCmd;
    
    function setMembershipId($id)
    {
        $this->membershipId = $id;
    }
    
    function setReason($reason)
    {
    	$this->reason = $reason;
    }
    
    function setOnSuccessCmd(Command $cmd){
        $this->onSuccessCmd = $cmd;
    }
    
    function getRequestVars()
    {
    	$vars = array('action' => 'RemoveMembership');
    	
    	if(isset($this->membershipId)) {
    		$vars['membership_id'] = $this->membershipId;
    	}
    	
    	if(isset($this->reason)) {
    		$vars['reason'] = $this->reason;
    	}
    	
    	return $vars;
    }
    
    function execute(CommandContext $context)
    {
        // If Global Lock is enabled then user can't remove memberships
        PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
        if(GlobalLock::isLocked() && !UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException(
                dgettext('sdr', GlobalLock::persistentMessage()));
        }

        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        
        $membership_id = $context->get('membership_id');
        $reason = $context->get('reason');
        
        if(empty($reason)) $reason = NULL;
        
        if(is_null($membership_id) || !isset($membership_id)){
            throw new InvalidArgumentException('Membership ID was not set.');
        }
        
        $membership = MembershipFactory::getMembershipById($membership_id);
        
        if(!is_a($membership, 'Membership')){
            throw new InvalidArgumentException('MembershipFactory returned something that was not a Membership.');
        }
        
        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        $manager = new OrganizationManager($membership->getOrganizationId());
        $manager->ifLocked('You may not remove membership because ');

        $removeType = new RemoveMembershipType($membership);

        // Make sure the user has permission to execute this command
        if($removeType->isOrg()) {
            if(!UserStatus::orgAdmin($membership->getOrganizationId())) {
                PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
                throw new PermissionException(
                    dgettext('sdr', 'You do not have permission to remove members.'));
            }
        } else {
            if($membership->getMember()->getUsername() != UserStatus::getUsername()) {
                PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
                    throw new PermissionException(dgettext('sdr', 'You do not have permission to remove the selected membership.'));
            }
        }

        if(!Term::isCurrentTermSelected() && !UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to modify SDR history.');
        }

        $member = $membership->getMember();
        $org = $membership->getOrganizationName();
        
        $membership->delete();
        
        $removeType->sendEmail($membership, $reason);
        $removeType->notify($membership);
        
        $context->goBack();
    }
}

?>
