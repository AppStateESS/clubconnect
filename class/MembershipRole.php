<?php

/**
 * MembershipRole - Provides functionality for associating/disassociating roles with memberhsips
 * @author Jeremy Booker
 */

class MembershipRole {
    
    public $membership_id;
    public $role_id;
    
    public function __construct($membership, $role)
    {
    	if(is_a($membership, 'Membership')) {
    		$this->membership_id = $membership->getId();
    	} else {
            $this->membership_id = $membership;
    	}
    	
    	if(is_a($role, 'Role')) {
    		$this->role_id = $role->getId();
    	} else {
            $this->role_id = $role;
    	}
    }
    
    public function save()
    {
        $db = new PHPWS_DB('sdr_membership_role');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }
    
    public function delete()
    {
        $db = new PHPWS_DB('sdr_membership_role');
        $db->addWhere('membership_id', $this->membership_id);
        $db->addWhere('role_id', $this->role_id);
        
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }
    
    /*********************
     * Static Helper Functions
     */
    
    public static function countRoles($membership_id)
    {
        $db = new PHPWS_DB('sdr_membership_role');
        $db->addWhere('membership_id', $membership_id);
        $result = $db->count();
        
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        
        return $result;
    }
}
