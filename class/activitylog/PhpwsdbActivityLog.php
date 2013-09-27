<?php

namespace sdr\activitylog;

use \PHPWS_DB;
use \PHPWS_Error;
use \Command;
use \Organization;
use \Member;
use \UserStatus;
use \DateTime;
use \Current_User;

/**
 * SDR ActivityLog
 * Handles logging of various activities
 * 
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PhpwsdbActivityLog implements ActivityLog {
    const PG_DATE = 'Y-m-d H:i:s';
    
    public function log(Command $command, Organization $organization = null,
            Member $member = null, $notes = null)
    {
        $entry = $this->createEntry($command, $organization, $member, $notes);
        $this->commit($entry);
    }
    
    public function createEntry(Command $command, Organization $organization = null,
            Member $member = null, $notes = null)
    {
        $ip         = $_SERVER['REMOTE_ADDR'];
        $username   = UserStatus::getUsername();
        $admin      = UserStatus::isAdmin();
        $occurred   = new DateTime();
        $httpmethod = $_SERVER['REQUEST_METHOD'];
        
        if(UserStatus::isMasquerading()) {
            $notes .= " Admin: " . Current_User::getUsername();
        }
        
        $command = $command->getAction();
        if(!is_null($organization)) {
            $organization = $organization->getId();
        }
        if(!is_null($member)) {
            $member = $member->getId();
        }
        
        return new ActivityLogEntry($ip, $username, $admin, $occurred, $httpmethod,
            $command, $organization, $member, $notes);
    }
    
    public function commit(ActivityLogEntry $entry)
    {
        $db = new PHPWS_DB('sdr_activity_log');
        $db->addValue('ip', $entry->getIp());
        $db->addValue('username', $entry->getUsername());
        $db->addValue('admin', $entry->getAdmin());
        $db->addValue('occurred',
                $entry->getOccurred()->format(self::PG_DATE));
        $db->addValue('httpmethod', $entry->getHttpMethod());
        $db->addValue('command', $entry->getCommand());
        $db->addValue('organization', $entry->getOrganization());
        $db->addValue('member', $entry->getMember());
        $db->addValue('notes', $entry->getNotes());
        
        $result = $db->insert();
        if(PHPWS_Error::logIfError($result)) {
            \PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new \DatabaseException($result);
        }
    }
    
    protected function loadEntry(array $values)
    {
        return new ActivityLogEntry(
                $values['ip'],
                $values['username'],
                $values['admin'] != 0,
                $values['httpmethod'],
                DateTime::createFromFormat(self::PG_DATE, $values['occurred']),
                $values['command'],
                $values['organization'],
                $values['member'],
                $values['notes']);
    }
}

?>
