<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');

class DenyOrganizationApplicationCommand extends Command
{
    private $app_id;

    public function getRequestVars()
    {
        $vars = array('action' => 'DenyOrganizationApplication');

        if(!is_null($this->app_id)) {
            $vars['app_id'] = $this->app_id;
        }

        return $vars;
    }

    public function setApplicationId($app_id)
    {
        $this->app_id = $app_id;
    }

    public function execute(CommandContext $context)
    {
        $this->app_id = $context->get('app_id');
        if(is_null($this->app_id)) {
            PHPWS_Core::initModClass('sdr', 'exception/InvalidArgumentException.php');
            throw new InvalidArgumentException('Please specify an application to approve.');
        }
        $app = new OrganizationApplication($this->app_id);

        if(!UserStatus::isUser()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You must be logged in to deny an organization registration.');
        }

        PHPWS_Core::initModClass('sdr', 'MemberFactory.php');
        $user = MemberFactory::fromLogin();
        if($user->getId() == $app->req_pres_id || $user->getId() == $app->req_advisor_id) {
            PHPWS_Core::initModClass('sdr', 'DenyOrganizationApplicationEmail.php');
            $email = new DenyOrganizationApplicationEmail($app, $user);
            $email->send();

            $app->admin_confirmed = null;
            $app->save();

            $cmd = CommandFactory::getCommand('ShowUserSummary');
            $cmd->redirect();
        }

        PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
        throw new PermissionException('You are not authorized to deny the selected organization registration.');
    }
}

?>
