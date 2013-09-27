<?php

abstract class MessageSender {
    
    protected $message;
    
    function __construct(Message $message)
    {
        $this->message = $message;
    }
    
    abstract function send();
}

?>