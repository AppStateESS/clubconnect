<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class RemoveMaskCommand extends Command
{
    public function execute(CommandContext $context)
    {
        if(!UserStatus::isMasquerading()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You are not currently masquerading as another user.');
        }

        $user = UserStatus::getUsername();

        UserStatus::removeMask();

        PHPWS_Core::initModClass('sdr', 'Member.php');
        $member = new Member(NULL, $user);

        $cmd = CommandFactory::getCommand('ShowMemberInfoCommand');
        $cmd->setMemberId($member->getId());
        $cmd->redirect();
    }
}

?>
