<?php

namespace sdr\uberadmin;

use \Member;
use \Organization;

/**
 * Description of UberAdmin
 *
 * @author jtickle
 */
interface UberAdmin {
    public function hasRights(Member $member);
    public function canWrite(Member $member, Organization $org);
    public function canRead(Member $member, Organization $org);
}

?>
