<?php

PHPWS_Core::initModClass('sdr', 'Message.php');
PHPWS_Core::initModClass('sdr', 'MessageSenderFactory.php');

class EmailMessage extends Message {

    private $toList;
    private $ccList = null;
    private $bccList = null;
    private $fromAddress;
    private $wrap = TRUE;
    
    function __construct($toUsername, $fromUsername, $toList, $fromAddress, $ccList, $bccList, $subject, $template, $tags = NULL, $wrap = TRUE)
    {
        parent::__construct($toUsername, $fromUsername, $subject, $template, $tags);
        
        $this->toList   = $toList;
        $this->ccList   = $ccList;
        $this->bccList  = $bccList;
        $this->fromAddress = $fromAddress;
        $this->wrap = $wrap;
        
    }
    
    public function setToList($toList){
        $this->toList = $toList;
    }
    
    public function setCCList($ccList){
        $this->ccList = $ccList;
    }
    
    public function send()
    {
        $sender = MessageSenderFactory::getSender($this);
        $sender->send();
    }
    
    public function getToList(){
        return $this->toList;
    }
    
    public function getCcList(){
        return $this->ccList;
    }
    
    public function getBccList(){
        return $this->bccList;
    }
    
    public function getFromAddress(){
        return $this->fromAddress;
    }

    public function wrap() {
        return $this->wrap;
    }
}

?>
