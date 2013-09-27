<?php

namespace sdr\activitylog;
use \DateTime;

/**
 * An entry in the Activity Log
 */

class ActivityLogEntry {
    protected $ip;
    protected $username;
    protected $admin;
    protected $occurred;
    protected $httpmethod;
    protected $command;
    protected $organization;
    protected $member;
    protected $notes;
    
    public function __construct($ip, $username, $admin, DateTime $occurred,
            $httpmethod, $command, $organization, $member, $notes)
    {
        $this->ip           = $ip;
        $this->username     = $username;
        $this->admin        = $admin;
        $this->occurred     = $occurred;
        $this->httpmethod   = $httpmethod;
        $this->command      = $command;
        $this->organization = $organization;
        $this->member       = $member;
        $this->notes        = $notes;
    }
    
    public function getIp()
    {
        return $this->ip;
    }
    
    public function getUsername()
    {
        return $this->username;
    }

    public function getAdmin()
    {
        return $this->admin;
    }
    
    public function getOccurred()
    {
        return $this->occurred;
    }

    public function getHttpMethod()
    {
        return $this->httpmethod;
    }
    
    public function getCommand()
    {
        return $this->command;
    }
    
    public function getOrganization()
    {
        return $this->organization;
    }
    
    public function getMember()
    {
        return $this->member;
    }
    
    public function getNotes()
    {
        return $this->notes;
    }
}

?>
