<?php

PHPWS_Core::initModClass('sdr', 'exception/CommandException.php');

class IllegalCommandException extends CommandException {
    
    public function __construct($message, $code = 0){
        parent::__construct($message, $code);
    }
}

?>