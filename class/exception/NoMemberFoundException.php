<?php
PHPWS_Core::initModClass('sdr', 'exception/SDRException.php');

class NoMemberFoundException extends SDRException {
    
    protected $user;
    
    public function __construct($message, $user, $code = 0){
        parent::__construct($message, $code);
        $this->user = $user;
    }
    
    public function __toString(){
        return $this->user . ': ' . $this->message;
    }
}

?>