<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class WearMaskCommand extends Command
{
    protected $username;

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getParams()
    {
        return array('username');
    }

    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to log in as another user.');
        }

        UserStatus::wearMask($this->username);

        $cmd = CommandFactory::getCommand('ShowUserSummary');
        $cmd->redirect();
    }
}

?>
