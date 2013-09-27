<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class ChooseAdminCommand extends Command
{
    public function getRequestVars()
    {
        $vars = array('action' => 'ChooseAdmin');
        return $vars;
    }
    
    public function getName()
    {
        return 'Switch to Admin';
    }
    
    public function execute(CommandContext $context)
    {
        if(!UserStatus::canAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to set your user type.');
        }
        
        UserStatus::chooseAdmin();
        
        $cmd = CommandFactory::getCommand('ShowAdminSummary');
        $cmd->redirect();
    }
}

?>
