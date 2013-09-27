<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class ChooseUserCommand extends Command
{
    public function getRequestVars()
    {
        $vars = array('action' => 'ChooseUser');
        return $vars;
    }
    
    public function getName()
    {
        return 'Switch to User';
    }
    
    public function execute(CommandContext $context)
    {
        if(!UserStatus::canAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to set your user type.');
        }
        
        UserStatus::chooseUser();
        
        $cmd = CommandFactory::getCommand('ShowUserSummary');
        $cmd->redirect();
    }
}

?>
