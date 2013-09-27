<?php

PHPWS_Core::initModClass('sdr', 'exception/SDRException.php');

class CreateMembershipException extends SDRException {
    
    protected $member;
    
    public function __construct($message, Member $member, $code = 0){
        parent::__construct($message, $code);
        
        $this->member = $member;
    }
    
    public function __toString(){
        return $this->member->getUserName() . ': ' . $this->message;
    }
}

?>