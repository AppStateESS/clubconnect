<?php

/**
 * Command class which handles adding multiple members to an org at one time
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class AddMultipleMembershipsCommand extends Command
{
    public $organizationId;

    public function setOrganizationId($id){
        $this->organizationId = $id;
    }

    function getRequestVars()
    {
        return array('action'=>'AddMultipleMemberships', 'organization_id'=>$this->organizationId);
    }

    function execute(CommandContext $context)
    {
        // Check permissions to even be here
      PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
      if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
	PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
	throw new PermissionException(
				      dgettext('sdr', GlobalLock::persistentMessage()));
      }
        if(!UserStatus::isUser() && !UserStatus::isAdmin()){
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied.');
        }

        if(!Term::isCurrentTermSelected() && !UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to modify SDR history.');
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'exception/NoMemberFoundException.php');
        PHPWS_Core::initModClass('sdr', 'AddMultipleMembershipsNotification.php');

        $org    = new Organization($context->get('organization_id'));
        $orgMgr = new OrganizationManager($org);
        
        $cmd = CommandFactory::getCommand('ShowOrganizationRoster');
        $orgMgr->ifLocked('You may not add members because ');
        
        $userArray = split("\n", $context->get('members'));
        
        $successCount = 0;
        $errors = array();

        if(UserStatus::isAdmin()){
            // if admin, force student approval
            $studentApproved = 1;
        }else{
            // if club officer, don't force approval
            $studentApproved = 0;
        }
        
        foreach($userArray as $user){
            $mbr    = null;
            $mbrId  = null;
            
            $user = trim($user);

            if(empty($user)) {
                continue;
            }
            
            if(preg_match("/^[0-9]{9}$/", $user)){
                $mbr = new Member($user);
            }else{
                $user = preg_replace('/@.*appstate.edu/', '', $user);    // Strip out @[whatever.]appstate.edu
                if(!preg_match("/^[0-9a-zA-Z]*$/", $user)) {
                    $errors[] = new NoMemberFoundException('Illegal characters in user name or Banner ID.', $user);
                    continue;
                }
                $mbr = new Member(null, $user);
            }
            
            $mbrId = $mbr->getId();
            $userName = $mbr->getUsername();
            
            if((is_null($mbrId) || !isset($mbrId)) || (is_null($userName) || !isset($userName))){
                $errors[] = new NoMemberFoundException('No student available with this user name or Banner ID.', isset($mbrId)?$mbrId:$userName);
                continue;
            }
            
            try{
                $membership = $orgMgr->addMember($mbr, Term::getSelectedTerm(), $studentApproved, 1);
            }catch(SDRException $e){
                $errors[] = $e;
                continue;
            }
            
            $successCount++;
        }

        $failCount = sizeof($errors);
        
        if($failCount > 0){
            //TODO: Use an AddMultipleMembershipsNotification here instead (have to fix PHP's serilization first).
            $content = "There were problems adding some members. Successfully added $successCount members. Failed to add $failCount members: <br />";

            $content .= '<ul>';
            
            foreach($errors as $e){
                $content .= '<li>';
                $content .= $e->__toString();
                $content .= '</li>';
            }
            
            $content .= '</ul>';
            
            NQ::simple('sdr', SDR_NOTIFICATION_ERROR, $content);
            $cmd = CommandFactory::getCommand('ShowAddMultipleMemberships');
        }else{
            $content = "Successfully added $successCount members.";
            
            NQ::simple('sdr', SDR_NOTIFICATION_SUCCESS, $content);
            $cmd = CommandFactory::getCommand('ShowOrganizationRoster');
        }
        
        $cmd->setOrganizationId($org->getId());
        $cmd->redirect();
    }
}
?>
