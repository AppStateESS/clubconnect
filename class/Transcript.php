<?php

PHPWS_Core::initModClass('sdr', 'Member.php');

class Transcript {
	
	private $student       = null;
	private $membershipSet = null;
	
    public function __construct(Member $student){
	    $this->student         = $student;
	    $this->membershipSet   = $student->getMembershipSet();
	}
	
    public function getStudent(){
        return $this->student;
    }
	
	public function getMembershipSet(){
		return $this->membershipSet;
	}
	
	public function getMembershipsByTerm()
	{
	    $memberships = $this->membershipSet->getMemberships();
	    $membershipsByTerm = array();
	    
	    foreach($memberships as $membership){
	        $membershipsByTerm[$membership->getTerm()][] = $membership;
	    }
	    
	    return $membershipsByTerm;
	}
}

?>
