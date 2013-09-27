<?php

  /**
   * SDR Lockable Command for use with Global Lock
   * @author Robert Bost <bostrt at appstate dot edu>
   */

PHPWS_Core::initModClass('sdr', 'Command.php');
PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
PHPWS_Core::initModClass('sdr', 'UserStatus.php');

abstract class LockableCommand extends Command
{
    public function getLink($text = null, $target = NULL, $cssClass = NULL, $title = NULL)
    {
        if(!GlobalLock::isLocked() || UserStatus::isAdmin()){
            return parent::getLink($text, $target, $cssClass, $title);
        } else {
            return null;
        }
    }
}

?>
