<?php

PHPWS_Core::initModClass('sdr', 'Message.php');

class MessageSenderFactory {
    
    public static function getSender(Message $message)
    {
        $class = get_class($message);
        
        $senderClass = $class . 'Sender';

        PHPWS_Core::initModClass('sdr', "$senderClass.php");
        
        return new $senderClass($message);
    }
}