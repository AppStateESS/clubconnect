<?php

namespace sdr\uberadmin;

use \Member;
use \Organization;
use \PHPWS_DB;
use \PHPWS_Error;

/**
 * Description of PhpwsdbUberAdmin
 *
 * @author jtickle
 */
class PhpwsdbUberAdmin implements UberAdmin
{
    public function canWrite(Member $member, Organization $org)
    {
        return isAllowed($member, $org, 'w');
    }
    
    public function canRead(Member $member, Organization $org)
    {
        return isAllowed($member, $org, 'r');
    }

    public function hasRights(Member $member)
    {
        $db = new PHPWS_DB('sdr_organization_uberadmin');
        $db->addWhere('member_id', $member->getId());
        $result = $db->count();

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result);
        }

        return $result > 0;
    }
    
    protected function isAllowed(Member $member, Organization $org, $type)
    {
        $db = new PHPWS_DB('sdr_organization_uberadmin');
        $db->addWhere('member_id', $member->getId());
        $db->addWhere('organization_id', $org->getId(), null, 'or', 'org');
        $db->addWhere('type_id', $org->getType(), null, 'or', 'org');
        
        if($type == 'w') {
            $db->addWhere('access', 'w');
        } else if($type == 'r') {
            $db->addWhere('access', array('r', 'w'));
        } else {
            throw new InvalidArgumentException(
                    "Type can only be 'r' or 'w', '$type' provided");
        }
        
        $db->setTestMode();
        $result = $db->count();
        
        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result);
        }
        
        return $result > 0;
    }
}

?>
