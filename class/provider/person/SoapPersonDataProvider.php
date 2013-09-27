<?php

namespace sdr\provider\person;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class SoapPersonDataProvider extends PersonDataProvider
{
    protected $soap;
    protected $username;
    protected $application;

    public function __construct(\SOAPClient $soap, $username, $application,
        PersonDataProvider $fallbackProvider = null,
        $ttl = null)
    {
        parent::__construct($fallbackProvider, $ttl);
        $this->soap        = $soap;
        $this->username    = $username;
        $this->application = $application;
    }

    public function getPersonByUsername($username)
    {
        $result = $this->soap->GetBannerID(array(
            'User'        => $this->username,
            'Application' => $this->application,
            'UserName'    => $username
        ));

        if(!isset($response->GetBannerIDResult)) {
            return $this->getFallbackProvider()->getPersonByUsername($username);
        }

        return $this->getPersonById($response->GetBannerIDResult);
    }

    public function getPersonById($id)
    {
        $result = $this->soap->GetDirectoryInfo(array(
            'UserName'    => $this->username,
            'Application' => $this->application,
            'BannerID'    => $id
        ));

        if(!isset($response->GetDirectoryInfoResult)) {
            return $this->getFallbackProvider()->getPersonById($id);
        }

        // @todo Test on someone who is simultaneously student and staff
        return $this->createMemento($response->GetDirectoryInfoResult);
    }

    public function clearCache()
    {
        // nothing to do here!
    }

    protected function createMemento($result)
    {
        $result = $result->DirectoryInfo;

        return new PersonMemento(
            $result->banner_id,
            $result->user_name,
            $result->email,
            $result->first_name,
            $result->middle_name,
            $result->last_name,
            null,
            null,
            $result->preferred_name
        );
    }
}

?>
