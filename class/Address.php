<?php

class Address
{
	public $id;
	public $student_id;
	public $type;
	public $sequence;
	public $line_one;
	public $line_two;
	public $line_three;
	public $city;
	public $county;
	public $state;
	public $zipcode;
	public $phone;
	
	public $_student;
	
	public function __construct($id = NULL)
	{
		if(is_null($id))
		    return;
		    
		$this->initByAddressId($id);
	}
	
	public function initByAddressId($id)
	{
		$this->id = $id;
		
		$db = new PHPWS_DB('sdr_address');
		$db->addWhere('id', $id);
		
		$result = $db->loadObject($this);
		if(PHPWS_Error::logIfError($result)) {
			$this->id = 0;
			PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
			throw new DatabaseException($result->toString());
		}
	}
	
	public function loadStudent(Student &$student)
	{
		$this->_student = $student;
	}
	
	public function formatAddress($phone = TRUE)
	{
		if($this->type == 'AB') {
			return 'ASU Box ' . $this->line_one;
		}
		
		$address = "{$this->line_one}<br />";
		
		if(!is_null($this->line_two) && !empty($this->line_two))
		    $address .= "{$this->line_two}<br />";
		
		if(!is_null($this->line_three) && !empty($this->line_three))
		    $address .= "($this->line_three}<br />";
		    
		$address .= "{$this->city}, {$this->state} {$this->zipcode}";
		
		if($phone && !is_null($this->phone) && !empty($this->phone))
		    $address .= "<br />{$this->phone}";
		    
		return $address;
	}
    
    public function getId()
    {
        return $this->id;
    }
	
	public function setId($id)
	{
		$this->id = $id;
	}
	
	public function getStudentId()
	{
		return $this->student_id;
	}
	
	public function setStudentId($student_id)
	{
		$this->student_id = $student_id;
	}
	
	public function getType()
	{
		return $this->type;
	}
	
	public function setType($type)
	{
		$this->type = $type;
	}
	
	public function getSequence()
	{
		return $this->sequence;
	}
	
	public function setSequence($seq)
	{
		$this->sequence = $sequence;
	}
	
	public function getLineOne()
	{
		return $this->line_one;
	}
	
	public function setLineOne($line_one)
	{
		$this->line_one = $line_one;
	}
	
	public function getLineTwo()
	{
		return $this->line_two;
	}
	
	public function setLineTwo($line_two)
	{
		$this->line_two = $line_two;
    }
    
    public function getLineThree()
    {
    	return $this->line_three;
    }
    
    public function setLineThree($line_three)
    {
    	$this->line_three = $line_three;
    }
    
    public function getCity()
    {
    	return $this->city;
    }
    
    public function setCity($city)
    {
    	$this->city = $city;
    }
    
    public function getState()
    {
    	return $this->state;
    }
    
    public function setState($state)
    {
    	$this->state = $state;
    }
    
    public function getZipcode()
    {
    	return $this->zipcode;
    }
    
    public function setZipcode($zipcode)
    {
    	$this->zipcode = $zipcode;
    }
    
    public function getPhone()
    {
    	return $this->phone;
    }
    
    public function setPhone($phone)
    {
    	$this->phone = $phone;
    }
}