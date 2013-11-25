<?php

class Student {

    public $id = null;
	public $gender = null;
	public $ethnicity = null;
	public $birthdate = null;
	public $citizen = null;
	public $date_enrolled = null;
	
    public $_errFlag = FALSE;
    public $_errMsg = NULL;
    public $_message = NULL;
    
    public $_addresses = NULL;
    
    public static final function GENDER_VALUES() {
    	return array(
            'M' => 'Male',
            'F' => 'Female',
    	    'N' => 'Other',
    	    'X' => 'Unknown'
        );
    }
    
    public static final function ETHNICITY_VALUES() {
        return array(
            'I' => 'American Indian or Alaskan Native',
            'O' => 'Asian/Asian American',
            'B' => 'Black/African American',
            'W' => 'Caucasian/White',
            'N' => 'Not Specified',
            'H' => 'Hispanic',
            'C' => 'Cuban American',
            'M' => 'Mexican American',
            'P' => 'Puerto Rican American - US',
            'R' => 'Puerto Rican American - PR',
            'X' => 'Multiracial'
        );
    }
    
    public static final function CITIZEN_VALUES() {
        return array(
            'Y' => 'US Citizen',
            'N' => 'Non-Resident Alien',
            'R' => 'Resident Alien',
            'X' => 'Unknown'
        );
    }

    public function __construct($id = null)
    {
        if(is_null($id)) return;

        $this->id = $id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('sdr_student');
        if(!SDR::throwDb($db->loadObject($this)))
            $this->id = -1;
    }

    // Fields not in the database
    private $membershipSet = NULL;
    
    public function loadAddresses($types = null)
    {
        if(!is_null($this->_addresses))
            return;
    	    
        $db = new PHPWS_DB('sdr_address');
        $db->addWhere('student_id', $this->id);
        $db->addOrder('sequence');

        if(!is_null($types))
            $db->addWhere('type',$types);

        PHPWS_Core::initModClass('sdr', 'Address.php');
        $result = $db->getObjects('Address');

        if(!is_null($result)){
            foreach($result as $r) {
                $r->loadStudent($this);
            }
        }
    	
        $this->_addresses = $result;
    }
    
    public function loadEnrollments()
    {
    }
    
    public function loadMemberships()
    {
    }

    public function isRegistered($term)
    {
        $db = new PHPWS_DB('sdr_student_registration');
        $db->addWhere('student_id', $this->id);
        $db->addWhere('term', $term);
        return $db->count() > 0;
    }

    /*******************************
     *  Accessor & Mutator Methods *
     *******************************/

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $id;
    }
    
    public function setGender($gender)
    {
    	if(!in_array($gender, array_keys(self::GENDER_VALUES()))) {
            \sdr\Environment::getInstance()->silentException(
                new InvalidArgumentException("$gender is not an allowed value for Gender."));
        }
        
        $this->gender = $gender;
    }
    
    public function getGender()
    {
    	return $this->gender;
    }
    
    public function setEthnicity($ethnicity)
    {
        if(!in_array($ethnicity, array_keys(self::ETHNICITY_VALUES()))) {
            \sdr\Environment::getInstance()->silentException(
                new InvalidArgumentException("$ethnicity is not an allowed value for Ethnicity."));
        }
    	$this->ethnicity = $ethnicity;
    }
    
    public function getEthnicity()
    {
    	return $this->ethnicity;
    }
    
    public function setBirthdate($date)
    {
    	// TODO: Verify.
    	$this->birthdate = $date;
    }
    
    public function getBirthdate()
    {
    	return $this->birthdate;
    }
    
    public function setCitizen($citizen)
    {
        if(!in_array($citizen, array_keys(self::CITIZEN_VALUES()))) {
            \sdr\Environment::getInstance()->silentException(
                new InvalidArgumentException("$citizen is not an allowed value for Citizen."));
        }
    	$this->citizen = $citizen;
    }
    
    public function getCitizen()
    {
    	return $this->citizen;
    }
    
    public function isCitizen()
    {
    	return $this->citizen == 'Y';
    }
    
    public function setDateEnrolled($date)
    {
    	// TODO: Verify.
    	$this->date_enrolled = $date;
    }
    
    public function getDateEnrolled()
    {
    	return $this->date_enrolled;
    }
    
    public function getAddresses($types = null)
    {
    	$this->loadAddresses($types);
    	return $this->_addresses;
    }
}
?>
