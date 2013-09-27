<?php

/**
 * ChangeRolesView - View responsible for creating the interface for allowing admins to change the "role" of a membership
 * @author Jeremy Booker
 */

class ChangeRolesView extends sdr\View {

    private $membership = null;
    private $term = null;
    
    function __construct(Membership $membership, $term)
    {
        $this->membership = $membership;
        $this->term = $term;
    }
    
    function show()
    {
        // Make sure the user has permission to be here
        if(!UserStatus::orgAdmin($this->membership->getOrganizationId())){
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException(
                dgettext('sdr', 'You do not have permission to change members\' roles.'));
        }
        
        // Make sure this membership is approved, if not then show a message
        if($this->membership->isAwaitingApproval()) {
            $tags['UNAPPROVED'] = ""; //dummy tag
            return PHPWS_Template::process($tags, 'sdr', 'changeRoles.tpl');
        }
        
        PHPWS_Core::initModClass('sdr', 'Role.php');
        
        javascript('modules/sdr/RoleEditor');
        
        // Get the list of roles
        $roleList = Role::getUserRoleListComplement($this->membership->getId());
        
        // Transform it into a format we can use for a drop down box later
        $roleDropList[0] = 'Choose Role';
        foreach($roleList as $role){
            // Do not show hidden roles in drop box
            if(!$role['hidden']){
                $roleDropList[$role['id']] = $role['title'];
            }
        }
        
        $tags['ORG_NAME']       = $this->membership->getOrganizationName();
        $tags['STUDENT_NAME']   = $this->membership->getMemberName();
        $tags['MEMBERSHIP_ID']  = $this->membership->getId();

        $roles = $this->membership->getRoles();
        
        if(!empty($roles)){
            foreach($roles as $role){
                $tags['ROLE_REPEAT'][] = array(
                    'ROLE_ID'    => $role->getId(),
                    'ROLE_TITLE' => $role->getTitle());
            }
        }else{
            $tags['NO_ROLES'] = ""; // dummy tag
        }
        
        $form = new PHPWS_Form('role_add_form');
        $form->addDropBox('role_drop_box', $roleDropList);
        $form->addHidden('membership_id', $this->membership->getId());
        
        $form->mergeTemplate($tags);
        $tags = $form->getTemplate();
        
        return PHPWS_Template::process($tags, 'sdr', 'changeRoles.tpl');
    }
}

?>
