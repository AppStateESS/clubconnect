<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConfigurationManager
 *
 * @author jtickle
 */
final class ConfigurationManager
{
    private static $INSTANCE;
    
    public static function getInstance()
    {
        if(!isset(self::$INSTANCE)) {
            self::$INSTANCE = new ConfigurationManager();
        }
        
        return self::$INSTANCE;
    }
    
    private $activityLog;
    private $uberAdmin;
    private $soap;
    
    private function __construct()
    {
        $this->activityLog = new \sdr\activitylog\PhpwsdbActivityLog();
        $this->uberAdmin   = new \sdr\uberadmin\PhpwsdbUberAdmin();

        PHPWS_Core::initModClass('sdr', 'Soap.php');
        $this->soap        = new Soap();
    }
    
    public function getActivityLog()
    {   
        return $this->activityLog;
    }
    
    public function getUberAdmin()
    {
        return $this->uberAdmin;
    }

    public function getSoap()
    {
        return $this->soap;
    }
}

?>
