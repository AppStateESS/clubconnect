<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class DefaultCommand extends Command
{
    public function execute(CommandContext $context)
    {
        if(UserStatus::isGuest()) {
            $cmd = CommandFactory::getCommand('ShowOrganizationBrowser');
        } else if(UserStatus::isAdmin()) {
            $cmd = CommandFactory::getCommand('ShowAdminSummary');
        } else {
            $cmd = CommandFactory::getCommand('ShowUserSummary');
        }

        $cmd->redirect();
    }
}

?>
