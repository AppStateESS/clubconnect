<?php

require_once(PHPWS_SOURCE_DIR . 'inc/sdr.defines.php');
PHPWS_Core::initModClass('sdr', 'SOAPException.php');

class Soap
{
    protected static $SDR;
    protected static $DIRECTORY;

    protected static function getSdr()
    {
        if(is_null(self::$SDR)) {
            self::$SDR = new SoapClient(SDR_SOAP);
        }

        return self::$SDR;
    }

    protected static function getDirectory()
    {
        if(is_null(self::$DIRECTORY)) {
            self::$DIRECTORY = new SoapClient(DIRECTORY_SOAP);
        }

        return self::$DIRECTORY;
    }

    public function getDirectoryInfo($bannerid)
    {
        $params = array(
            'UserName'    => UserStatus::getUsername(),
            'Application' => 'SDR',
            'BannerID'    => $bannerid
        );

        try {
            $result = self::getDirectory()->GetDirectoryInfo($params);
        } catch(SoapFault $e) {
            Soap::logSoap('GetDirectoryInfo', 'failure', $params, $e);
            return false;
        }

        if(!isset($result->GetDirectoryInfoResult->DirectoryInfo)) {
            Soap::logSoap('GetDirectoryInfo', 'failure', $params, 'DirectoryInfo empty');
            return false;
        }

        Soap::logSoap('GetDirectoryInfo', 'success', $params);
        return $result->GetDirectoryInfoResult->DirectoryInfo;
    }

    public function getBannerId($username)
    {
        $params = array(
            'User'        => UserStatus::getUsername(),
            'Application' => 'SDR',
            'UserName'    => $username
        );

        try {
            $result = self::getDirectory()->GetBannerId($params);
        } catch(SoapFault $e) {
            Soap::logSoap('GetBannerID', 'failure', $params, $e);
            return false;
        }

        if($result->GetBannerIDResult == 'InvalidUserName') {
            Soap::logSoap('GetBannerID', 'failure', $params, 'InvalidUserName');
            return false;
        }

        Soap::logSoap('GetBannerID', 'success', $params);
        return $result['GetBannerIDResult'];
    }

    protected static function logSoap($function, $result, array $params, $fault = null)
    {
        $args = implode(', ', $params);
        $msg = "$function($args) result: $result";
        if($fault instanceof SoapFault) {
            $msg .= ' ' . $fault->getCode() . ' ' . $fault->getMessage();
        } else if (!is_null($fault)) {
            $msg .= ' ' . $fault;
        }
        PHPWS_Core::log($msg, 'soap.log', 'SOAP');
    }
}

?>
