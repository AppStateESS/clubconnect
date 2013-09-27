<?php

namespace sdr\activitylog;
use \Command;
use \Organization;
use \Member;

/**
 * ActivityLog provides a way to log all activities within SDR.  This interface
 * can be extended to write to a database, syslog, text file, or whatever else
 * the programmer may dream up.
 *
 * @author    Jeff Tickle <jtickle@tux.appstate.edu>
 * @copyright 2005-2012 Appalachian State University
 * @license   https://www.gnu.org/licenses/gpl.html GNU General Public License
 */
interface ActivityLog
{
    /**
     */
    public function log(
        Command      $command,
        Organization $organization = null,
        Member       $member       = null,
                     $notes        = null
    );
    public function createEntry(
        Command      $command,
        Organization $organization = null,
        Member       $member = null,
                     $notes = null
    );
    public function commit(ActivityLogEntry $entry);
}

?>
