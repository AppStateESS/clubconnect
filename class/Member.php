<?php

/**
 * SDR Member Class
 *
 * Every student and advisor can be a member of an organization and can
 * administer an organization.  We treat Advisors as a certain type of
 * Member.  In most cases, the extra data provided by Student and
 * Advisor may not be necessary, and this will be sufficient, and
 * that's the only reason it's not abstract.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Student.php');
PHPWS_Core::initModClass('sdr', 'Advisor.php');

class Member
{
	public $id = null;
	public $username = null;
	public $prefix = null;
	public $first_name = null;
	public $middle_name = null;
	public $last_name = null;
	public $suffix = null;
    public $_advisor = null;
    public $_student = null;
    public $datasource = null;
	
	// Fields not in the database
	private $membershipSet = NULL;
	
    /**
     *  Constructor - Can be called with either a member_id or a username. If
     *  both are given, the member_id is used.
     * @param $member_id - member_id from 'members' table
     * @param $username - asu username
     * @return void
     */
	public function __construct($id = NULL, $username = NULL)
	{
		if(is_null($id) && is_null($username)) {
			return;
		}
		
		if($id) {
			$this->initById($id);
		} else {
			$this->initByUsername($username);
		}
	}
	
	protected function initById($id)
	{
		/*$this->id = $id;

        $soap = ConfigurationManager::getInstance()->getSoap();
        $result = $soap->getDirectoryInfo($id);

        if($result !== FALSE) {
            $this->id          = $result->banner_id;
            $this->username    = $result->user_name;
            $this->first_name  = $result->first_name;
            $this->middle_name = $result->middle_name;
            $this->last_name   = $result->last_name;

            if($result->type == 'Staff') {
                $this->_advisor = new Advisor();
                $this->_advisor->setId($this->id);
                $this->_advisor->setOfficePhone($result->phone);
                $this->_advisor->setOfficeLocation($result->title);
                $this->_advisor->setDepartment($result->deptmajor);
            }

            $this->datasource = 'soap';
        } else {*/
            $db = new PHPWS_DB('sdr_member');
            $db->addWhere('id', $id);

            $this->init($db);
        //}
	}
    
    protected function initByUsername($username)
    {
        /*$soap = ConfigurationManager::getInstance()->getSoap();
        $result = $soap->getBannerId($username);

        if($result !== FALSE) {
            $this->initById($result);
        } else {*/
            $this->username = $username;
            
            $db = new PHPWS_DB('sdr_member');
            $db->addWhere('username', $username);
            
            $this->init($db);
        //}
    }
	
	protected function init(PHPWS_DB $db)
	{
        SDR::throwDb($db->loadObject($this));
        
        $this->loadStudent();
        $this->loadAdvisor();

        $this->datasource = 'db';
	}

    public function loadStudent()
    {
        $student = new Student($this->id);
        if($student->id == $this->id) $this->_student = $student;
    }

    public function loadAdvisor()
    {
        $advisor = new Advisor($this->id);
        if($advisor->id == $this->id) $this->_advisor = $advisor;
    }
	
	public function save()
	{
		PHPWS_DB::begin();

		// Save Member Part
		$db = new PHPWS_DB('sdr_member');
        SDR::throwDb($db->saveObject($this));

        if($this->_student) {
            $this->_student->setId($this->id);
            $this->_student->save();
        }
        if($this->_advisor) {
            $this->_advisor->setId($this->id);
            $this->_advisor->save();
        }
		
		PHPWS_DB::commit();
		
		return $this->id;
	}
    
    function searchResultsPagerTags($studentSelectedCmd)
    {
        $tags = array();
        
        $tags['NAME'] = $this->getLastNameFirst();
        
        $username = $this->getUsername();
        
        if(isset($username) && $username != ''){
            $tags['EMAIL'] = $username . '@appstate.edu';
        }else{
            $tags['EMAIL'] = 'Unknown';
        }
        
        if(UserStatus::isAdmin()) {
            $tags['BANNER_ID']  = $this->getId();
        }

        $studentSelectedCmd->setMemberId($this->getId());
        
        $tags['ACTIONS'] = $studentSelectedCmd->getLink('Select');

        if($this->_advisor) {
            $tags['ADVISOR_CLASS'] = 'advisor';
        }
        
        return $tags;
    }
	
	public function getMembershipSet()
	{
		PHPWS_Core::initModClass('sdr', 'MembershipSet.php');
         
        # If we've already looked them up, don't do it again
        if(!is_null($this->membershipSet)){
            return $this->membershipSet;
        }
         
        $this->membershipSet = new MembershipSet($this->id);
         
        return $this->membershipSet;
	}

    public function linkToProfile($string)
    {
        $cmd = CommandFactory::getInstance()->getCommand('ShowMemberInfo');
        $cmd->setMemberId($this->id);
        return $cmd->getLink($string);
    }
	
	public function getFullName()
	{
		$name = $this->last_name;
		
		if(!empty($this->middle_name))
		    $name = "{$this->middle_name} $name";
		    
		$name = "{$this->first_name} $name";
		
		if(!empty($this->prefix))
		    $name = "{$this->prefix} $name";
		    
		if(!empty($this->suffix))
		    $name = "$name, {$this->suffix}";
		
		return $name;
	}
	
	public function getLastNameFirst()
	{
		$name = "{$this->last_name},";
		
		if(!empty($this->prefix)) {
			$name .= " {$this->prefix}";
		}
		
		$name .= " {$this->first_name}";
		
		if(!empty($this->middle_name)) {
			$name .= " {$this->middle_name}";
		}
		
		if(!empty($this->suffix)) {
			$name .= ", {$this->suffix}";
		}
		
		return $name;
	}

	public function getFriendlyName()
	{
		return "{$this->first_name} {$this->last_name}";
	}
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function setUsername($username)
	{
		$this->username = $username;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
	}
	
	public function getPrefix()
	{
		return $this->prefix;
	}
	
	public function setFirstName($first_name)
	{
		$this->first_name = $first_name;
	}
	
	public function getFirstName()
	{
		return $this->first_name;
	}
	
	public function setMiddleName($middle_name)
	{
		$this->middle_name = $middle_name;
	}
	
	public function getMiddleName()
	{
		return $this->middle_name;
	}
	
	public function setLastName($last_name)
	{
		$this->last_name = $last_name;
	}
	
	public function getLastName()
	{
		return $this->last_name;
	}
	
	public function setSuffix($suffix)
	{
		$this->suffix = $suffix;
	}
	
	public function getSuffix()
	{
		return $this->suffix;
    }

    public function getStudent()
    {
        return $this->_student;
    }

    public function setStudent(Student $s)
    {
        $this->_student = $s;
    }

    public function isStudent()
    {
        return !!$this->_student;
    }
	
	public function getAdvisor()
	{
		return $this->_advisor;
    }

    public function setAdvisor(Advisor $a)
    {
        $this->_advisor = $a;
    }

    public function isAdvisor()
    {
        return !!$this->_advisor;
    }
}

?>
