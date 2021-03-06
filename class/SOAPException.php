<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class SOAPException extends \Exception {
    public function __construct($message, $code = 0, $functionName, $params) {
        parent::__construct($message, $code);
        $errorMsg = $functionName . '(' . implode(',', $params) . '): ' . $code;
        PHPWS_Core::log($errorMsg, 'soapError.log', ('BannerError'));
    }
}

?>
