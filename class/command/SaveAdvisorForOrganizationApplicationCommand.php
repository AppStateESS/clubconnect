<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class SaveAdvisorForOrganizationApplicationCommand extends Command
{
    private $app_id;

    public function getRequestVars()
    {
        $vars = array('action' => 'SaveAdvisorForOrganizationApplication');

        if(!is_null($this->app_id)) {
            $vars['app_id'] = $this->app_id;
        }

        return $vars;
    }

    public function setApplicationId($id)
    {
        $this->app_id = $id;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You must be an admin to change a Member record.');
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
        $app = new OrganizationApplication($context->get('app_id'));

        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'Advisor.php');

        $m = new Member();
        $context->plugObject($m);
        $a = new Advisor();
        $context->plugObject($a);
        $m->setAdvisor($a);

        $m->save();

        $app->req_advisor_id = $m->getId();
        $app->save();

        $cmd = CommandFactory::getCommand('ApproveOrganizationApplication');
        $cmd->setApplicationId($app->id);
        $cmd->redirect();
    }
}

?>
