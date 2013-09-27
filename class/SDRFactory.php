<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'UserStatus.php');

class SDRFactory
{
    private static $sdr;

    static function getSDR()
    {
        $rh = getallheaders();
        if(isset(SDRFactory::$sdr)) {
            return SDRFactory::$sdr;
        } else 

        // try to determine if it's an AJAX request, lol
        if(isset($_REQUEST['ajax']) 
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            || isset($_REQUEST['callback'])
            || (array_key_exists('Content-Type', $rh)
                && stripos($rh['Content-Type'], 'application/json') !== FALSE)) {
            PHPWS_Core::initModClass('sdr', 'AjaxSDR.php');
            SDRFactory::$sdr = new AjaxSDR();
        } else
            
        if(UserStatus::isUser() || UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'UserSDR.php');
            SDRFactory::$sdr = new UserSDR();
        } else

        // Guest
        {
            PHPWS_Core::initModClass('sdr', 'GuestSDR.php');
            SDRFactory::$sdr = new GuestSDR();
        }

        return SDRFactory::$sdr;
    }
}

?>
