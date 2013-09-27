<?php

/**
 * SDR Organization Roster View
 * Fancy new organization roster management view.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Member.php');

class OrganizationRosterView extends sdr\View
{
    private $organization_id;
    private $organization;
	private $memberships;
	private $term;
	private $member; // Holds a member object for whomever is logged in
	
	
	public function __construct(Organization $organization, $memberships = null, $term = null)
	{
        $this->organization_id = $organization->getId();
        $this->organization    = $organization;
	    
		if(isset($memberships))
		    $this->memberships = $memberships;
		    
		if(isset($term))
		    $this->term = $term;
		    
		$this->member = new Member(null, UserStatus::getUsername());
	}
	
	public function setMemberships($memberships)
	{
		$this->memberships = $memberships;
	}
	
	public function setTerm($term)
	{
		$this->term = $term;
	}
	
	protected function resolveLevel(Membership $membership)
	{
		switch($membership->getLevel()) {
			case MBR_LEVEL_AWAITING_ORG:
				return 'awaiting-org';
			case MBR_LEVEL_AWAITING_STUDENT:
				return 'awaiting-student';
			case MBR_LEVEL_OFFICER:
				return 'officer';
			case MBR_LEVEL_MEMBER:
				return 'member';
			case MBR_LEVEL_ADVISOR:
				return 'officer';
		}
		
		return 'member';
	}
	
	protected function formatName(Membership $membership)
	{
		$first = $membership->_member_first_name;
		$middle = $membership->_member_middle_name;
		$last = $membership->_member_last_name;
		
		return "$last, $first $middle";
	}
	
	protected function formatRoles(Membership $membership)
	{
		$modified = date('m/d/y', $membership->getLastApproval());
		
		if($membership->isAwaitingApproval()) {
			return "Pending since $modified";
		}
		
		return $membership->getRolesConcat();
	}
    
    protected function roleChangeLink(Membership $membership)
    {
        # If the membership is awaiting approval, don't show the "change role" link
        if($membership->isAwaitingApproval()) {
            return "";
        }

        if(!UserStatus::isAdmin() && $membership->isAdvisor()) {
            return "";
        }
        
        $cmd = CommandFactory::getCommand('ShowChangeRoles');
        $cmd->setMembershipId($membership->getId());
	if(!is_null($cmd->getLink('Change'))){
	  return '[' . $cmd->getLink('Change') . ']';
	} else {
	  return null;
	}
    }
	
    protected function getActions(Membership $membership)
    {
      $actions = array(); // Array for storing actions as they're created
	    
      $mbrLevel = $membership->getLevel();
      
	// If the student is awaiting approval to join, show a link to the approve and decline commands
      if($mbrLevel == MBR_LEVEL_AWAITING_ORG){
          $approveCmd = CommandFactory::getCommand('ApproveMembership');
          $approveCmd->setMembershipId($membership->getId());
          if(!is_null($approveCmd->getLink('Approve'))){
              $actions[] = '[' . $approveCmd->getLink('Approve') . ']';
          }

          $declineCmd = CommandFactory::getCommand('RemoveMembershipConfirmation');
          $declineCmd->setMembershipId($membership->getId());
          if(!is_null($declineCmd->getLink('Decline'))){
              $actions[] = '[' . $declineCmd->getLink('Decline') . ']';
          }
      } else {

          // If we are awaiting the student's approval, show a link to cancel the request
          if($mbrLevel == MBR_LEVEL_AWAITING_STUDENT){
              $cancelCmd = CommandFactory::getCommand('RemoveMembershipConfirmation');
              $cancelCmd->setMembershipId($membership->getId());
              $actions[] = '[' . $cancelCmd->getLink('Cancel Request') . ']';
          } else {

              // If the membership has been approved, the admin can remove it unless it is the
              // admin's own membership record, unless of course the admin is 
              // actually an SDR administrator
              if($membership->getMemberId() != $this->member->getId() || UserStatus::isAdmin()){
                  $rmCmd = CommandFactory::getCommand('RemoveMembershipConfirmation');
                  $rmCmd->setMembershipId($membership->getId());
                  if(!is_null($rmCmd->getLink('Remove'))){
                      $actions[] = '[' . $rmCmd->getLink('Remove') . ']';
                  }
              }
          }
      }

      return implode(' ', $actions);
    }
	
	// Shows the admin status of each member, allows the status to be changed if user is an admin
	protected function formatAdminIcons(Membership $membership, $isAdmin = FALSE)
	{
        if($membership->getLevel() == MBR_LEVEL_AWAITING_STUDENT ||
           $membership->getLevel() == MBR_LEVEL_AWAITING_ORG) {
            return;
        }

	    if($isAdmin){
	        // User has permission to change admin status
	        if($membership->isAdministrator()){
                return "<a id=\"{$membership->getId()}\"href=\"javascript:removeAdmin({$membership->getId()});\"><img width=\"16\" height=\"16\" src=\"".PHPWS_SOURCE_HTTP."mod/sdr/img/tango-icons/emblems/emblem-system.png\"></a>";
	        }else{
	            return "<a id=\"{$membership->getId()}\"href=\"javascript:addAdmin({$membership->getId()});\"><img width=\"16\" height=\"16\" src=\"".PHPWS_SOURCE_HTTP."mod/sdr/img/tango-icons/emblems/emblem-unreadable.png\"></a>";
	        }
        }else{
	        // User cannot change admin status, only view it
	        if($membership->isAdministrator()){
                return '<img width="18" height="18" src="'.PHPWS_SOURCE_HTTP.'mod/sdr/img/tango-icons/emblems/emblem-system.png">';
            }
	    }
	}
	
	public function show()
	{
		$tpl = new PHPWS_Template('sdr');
		$result = $tpl->setFile('OrganizationRosterView.tpl');
		if(PHPWS_Error::logIfError($result)) {
			throw new Exception('Unknown template error in OrganizationRosterView');
		}

        $instance = $this->organization->getInstance(Term::getSelectedTerm());
        if(is_null($instance->getId())) {
            $tpl->setCurrentBlock('UNREGISTERED');
            $tpl->setData(array('UNREG_TERM' => Term::getPrintableSelectedTerm()));
            $tpl->parseCurrentBlock();

            return $tpl->get();
        }

		$canChangeHistory = Term::isCurrentTermSelected() || UserStatus::isAdmin();
		
        if($canChangeHistory) {
            $singleAddCmd = CommandFactory::getCommand('ShowStudentSearch');
            $singleAddCmd->setOrganizationId($this->organization_id);
            
            $multiAddCmd = CommandFactory::getCommand('ShowAddMultipleMemberships');
            $multiAddCmd->setOrganizationId($this->organization_id);
            
            $tpl->setData(array('ADD_MBR_LINK'=>$singleAddCmd->getLink('Add Member'),
				'ADD_MULT_MBRS_LINK'=>$multiAddCmd->getLink('Add Multiple Members')));
        }
		
		if(count($this->memberships) == 0) {
			$tpl->setCurrentBlock('EMPTY_ROSTER');
			$tpl->setData(array('EMPTY_MESSAGE' =>
			    dgettext('sdr', 'No one is currently a member of this organization.')));
		    $tpl->parseCurrentBlock();
		    
		    return $tpl->get();
		}
		
		$isAdmin = UserStatus::isAdmin();
		
		if($isAdmin){
		    javascript('modules/sdr/AdministratorToggle');
		}
		
		javascript('modules/sdr/RemovalConfirmationPopup');
		
		$tpl->setCurrentBlock('ROSTER');
		foreach($this->memberships as $membership) {
			$tpl->setCurrentBlock('MEMBER');
            $data = array(
			    'LEVEL'  => self::resolveLevel($membership),
			    'NAME'   => $membership->getMember()->getLastNameFirst(),
			    'ROLE'   => self::formatRoles($membership));
            if($canChangeHistory) {
			    $data['ADMIN']       = self::formatAdminIcons($membership,$isAdmin);
			    $data['ROLE_CHANGE'] = self::roleChangeLink($membership);
			    $data['ACTIONS']     = self::getActions($membership);
            }
            $tpl->setData($data);
			$tpl->parseCurrentBlock();
		}
        $data = array(
            'NAME_HDR'      => dgettext('sdr', 'Name'),
            'ROLE_HDR'      => dgettext('sdr', 'Role'));
        if($canChangeHistory) {
            $data['ADMIN_HDR']   = dgettext('sdr', 'Admin');
            $data['ACTIONS_HDR'] = dgettext('sdr', 'Actions');
        }
	
        $tpl->setData($data);
	$tpl->parseCurrentBlock();
		
		return $tpl->get();
	}
}
