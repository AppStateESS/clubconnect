<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowCreateMemberCommand extends Command
{

    function getRequestVars()
    {
        $vars = array('action' => 'ShowCreateMember');

        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You must be an admin to create a new member record.');
        }

        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'Advisor.php');
        $m = new Member();
        $context->plugObject($m);
        $a = new Advisor();
        $context->plugObject($a);
        $m->setAdvisor($a);

        PHPWS_Core::initModClass('sdr', 'EditMemberView.php');
        $view = new EditMemberView($m, CommandFactory::getCommand('SaveMember'));

        $context->setContent($view->show());
    }
}

?>
