<?php

/**
 * Role class - Stores the various data elements of the Role table and and provides load/save functions
 * @author Jeremy Booker
 */

define('ROLE_RANK_PLEDGE',  -1);
define('ROLE_RANK_MEMBER',   0);
define('ROLE_RANK_OFFICER3',  1);
define('ROLE_RANK_OFFICER2', 2);
define('ROLE_RANK_OFFICER1', 3);
define('ROLE_RANK_ADVISOR',  10);

class Role {
    
    public $id;
    public $title;
    public $rank;
    public $hidden;
    
    public function __construct($id = null)
    {
        if(!is_null($id) && isset($id)){
            $this->id = $id;
            $this->load();
        }
    }
    
    public static function getAdvisorRole()
    {
    	$db = new PHPWS_DB('sdr_role');
    	$db->addWhere('rank', ROLE_RANK_ADVISOR);
    	$result = $db->getObjects('Role');
    	
    	return $result[0];
    }

    public static function getPresidentRole()
    {
        $db = new PHPWS_DB('sdr_role');
        $db->addWhere('title', 'President');    // TODO: This is horrible.
        $result = $db->getObjects('Role');
        return $result[0];
    }
    
    public function load()
    {
        if(is_null($this->id)){
            return;
        }
        
        $db = new PHPWS_DB('sdr_role');
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }
    
    public function save()
    {
        $db = new PHPWS_DB('sdr_role');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
    	$this->id = $id;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
    	$this->title = $title;
    }
    
    public function getRank()
    {
    	return $this->rank;
    }
    
    public function setRank($rank)
    {
    	$this->rank = $rank;
    }

    public function getHidden()
    {
      return $this->hidden;
    }

    public function setHidden($hidden)
    {
      $this->hidden = $hidden;
    }
    
    public function isPledge()
    {
    	return $this->getRank() == ROLE_RANK_PLEDGE;
    }
    
    public function isMember()
    {
    	return $this->getRank() == ROLE_RANK_MEMBER;
    }
    
    public function isOfficer()
    {
    	return $this->getRank() == ROLE_RANK_OFFICER1 ||
    	       $this->getRank() == ROLE_RANK_OFFICER2 ||
    	       $this->getRank() == ROLE_RANK_OFFICER3;
    }
    
    public function isAdvisor()
    {
    	return $this->getRank() == ROLE_RANK_ADVISOR;
    }
    
    public function __toString()
    {
        return $this->title;
    }

    public function getRowTags()
    {
      $tags = array();

      $tags['TITLE'] = $this->getTitle();
      $rank = $this->getRank();
      $rankName = & $tags['RANK'];
      switch($rank){
      case -1:
          $rankName = 'Pre-Member ("Pledge")';
          break;
      case 0:
          $rankName = 'Member';
          break;
      case 1:
          $rankName = 'Low-level Officer';
          break;
      case 2:
          $rankName = 'Medium-level Officer';
          break;
      case 3:
          $rankName = 'High-level Officer';
          break;
      case 10:
          $rankName = 'Advisor';
      }
      

      if(!$this->getHidden()){
          $tags['HIDDEN'] = 'No';
          $tags['HIDDEN_CLASS'] = 'advisor';
          $hideCmd = CommandFactory::getCommand('HideRole');
          $hideCmd->setRoleId($this->id);
          $tags['HIDE'] = $hideCmd->getLink('Hide');
      } else {
          $tags['HIDDEN'] = 'Yes';
          $tags['HIDDEN_CLASS'] = 'hidden';
          $showCmd = CommandFactory::getCommand('ShowRole');
          $showCmd->setRoleId($this->id);
          $tags['SHOW'] = $showCmd->getLink('Show');
      }
      

      return $tags;
    }
    
    /**************************
     *  Static Helper Methods *
     **************************/
    
    /**
     * Returns an associate array of all roles where role id => role name
     * (Probably doesn't belong is this class, but there isn't anywhere better for it right now.)
     * @return Array Array of role names with the role id as the key
     */
    public static function getRoleList(){
        $db = new PHPWS_DB('sdr_role');
        $db->addOrder('title ASC'); // Alphabetical order by title
        return $db->select();
    }
    
    /**
     * Returns an associate array of all roles (for the given membership id) which the member
     * does *not* hold.
     * @param $membership_id Membership id
     * @return Array Associate array of roles
     */
    public static function getUserRoleListComplement($membership_id)
    {
    	$extra = '';
    	if(!UserStatus::isAdmin()) {
    		$extra = 'AND rank < 10';
    	}
        $result = PHPWS_DB::getAll("select sdr_role.* from sdr_role where id NOT IN (select id from sdr_membership_role JOIN sdr_role ON sdr_membership_role.role_id = sdr_role.id WHERE membership_id=$membership_id) $extra order by title");

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    public static function roleExistsByTitle($title)
    {
        $db = new PHPWS_DB('sdr_role');
        $db->addColumn('title');
        $result = $db->select();

        // Check titles. Not case-sensitive        
        foreach($result as $t){
            if(strcasecmp($t['title'], $title) == 0){
                return 1;
            }
        } 
        return 0;
    }
}

?>
