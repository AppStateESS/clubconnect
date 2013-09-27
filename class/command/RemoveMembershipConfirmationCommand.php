<?php

/**
 * Show confirmation before removing/canceling/declining membership
 * @author Jeff Tickle
 */

PHPWS_Core::initModClass('sdr', 'LockableCommand.php');
PHPWS_Core::initModClass('sdr', 'JavascriptCommand.php');
PHPWS_Core::initModClass('sdr', 'RemoveMembershipType.php');

class RemoveMembershipConfirmationCommand extends LockableCommand implements JavascriptCommand
{
	private $membershipId;
	
	function setMembershipId($id)
	{
		$this->membershipId = $id;
	}
	
	function getRequestVars()
	{
		$vars = array('action' => 'RemoveMembershipConfirmation');
		
		if(isset($this->membershipId)) {
			$vars['membership_id'] = $this->membershipId;
		}
		
		return $vars;
	}
	
	function getLink($text = NULL, $target = NULL, $cssClass = NULL, $title = NULL)
	{
	  // if parent::getLink returns null then
	  // global lock must be on
	  if(is_null(parent::getLink($text))){
	    return null;
	  }

	  $link = new PHPWS_Link(dgettext('sdr', $text), 'sdr', self::getRequestVars(), true);
	  $link->setOnClick('removeMembershipConfirmationJS(' . $this->membershipId . '); return false;');
	    
	  return $link->get();
	}
    
    function getJsCallback()
    {
        PHPWS_Core::initModClass('sdr', 'exception/UnsupportedFunctionException.php');
        throw new UnsupportedFunctionException('getJsCallback is unsupported by this Command');
    }
    
    function getJavascript()
    {
        // TODO: Implement
    }
    
    function execute(CommandContext $context)
    {
    	if(isset($this->membershipId)) {
    		$membership_id = $this->membershipId;
    	} else {
    	    $membership_id = $context->get('membership_id');
    	}
    	
    	if(is_null($membership_id) || !isset($membership_id)) {
            throw new InvalidArgumentException('Membership ID was not set.');
    	}
    	
        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
    	$membership = MembershipFactory::getMembershipById($membership_id);
    	
    	if(!is_a($membership, 'Membership')) {
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
                return;
            }
        } else {
            if($membership->getMember()->getUsername() != UserStatus::getUsername()) {
                PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
                throw new PermissionException(
                    dgettext('sdr', 'You do not have permission to remove the selected membership.'));
                return;
            }
        }
        
        $remove = CommandFactory::getCommand('RemoveMembership');
        $remove->setMembershipId($membership_id);

        PHPWS_Core::initModClass('sdr', 'RemoveConfirmationView.php');
        $cr = new RemoveConfirmationView($membership, $remove);
        
        $context->preventPushContext();
        $context->pleaseDontJson();
        $context->setContent($cr->show());
    }
}
