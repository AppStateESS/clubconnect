<?php

namespace sdr\provider;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class MagicProviderFactory
{
    private static $INSTANCE;

    public static function getInstance()
    {
        if(is_null(self::$INSTANCE)) {
            $configFile = PHPWS_SOURCE_DIR . 'inc/sdrconfig.ini.php';
            self::$INSTANCE = new MagicProviderFactory($configFile);
        }

        return self::$INSTANCE;
    }

    protected $person;

    private function __construct($configFile)
    {
        if(!file_exists($configFile)) {
            throw new \Exception($configFile . ' does not exist.  Please copy it from SDR.');
        }

        $config = parse_ini_file($configFile, true);
        $sdrSoap       = new SoapClient($config['SOAP']['sdr'] . '?WSDL');
        $directorySoap = new SoapClient($config['SOAP']['directory'] . '?WSDL');
        
        $this->person =
            new person\APCPersonDataProvider(
                new person\PHPWSDBPersonDataProvider(
                    new SoapPersonDataProvider(
                        $directorySoap, UserStatus::getUsername(), 'sdr', null
                    ), null
                ), null
            ), null
        );
    }

    public function getPersonDataProvider()
    {
        return $this->person;
    }
}

?>
