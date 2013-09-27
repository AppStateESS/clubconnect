<?php
PHPWS_Core::initModClass('sdr', 'exception/SDRException.php');

class OrganizationNotFoundException extends SDRException {
    
    protected $org;
    
    public function __construct($message, $org, $code = 0){
        parent::__construct($message, $code);
        $this->org = $org;
    }
    
    public function __toString(){
        return $this->org . ': ' . $this->message;
    }
}

?>