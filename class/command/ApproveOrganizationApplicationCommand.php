<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
PHPWS_Core::initModClass('sdr', 'EmailMessage.php');

class ApproveOrganizationApplicationCommand extends Command
{
    private $app_id;

    public function getRequestVars()
    {
        $vars = array('action' => 'ApproveOrganizationApplication');

        if(!is_null($this->app_id)) {
            $vars['app_id'] = $this->app_id;
        }

        return $vars;
    }

    public function setApplicationId($app)
    {
        $this->app_id = $app;
    }

    public function execute(CommandContext $context)
    {
        $this->app_id = $context->get('app_id');
        if(is_null($this->app_id)) {
            PHPWS_Core::initModClass('sdr', 'exception/InvalidArgumentException.php');
            throw new InvalidArgumentException('Please specify an application to approve.');
        }
        $app = new OrganizationApplication($this->app_id);

        if(UserStatus::isAdmin()) {
            if(is_null($app->req_advisor_id)) {
                NQ::Simple('sdr', SDR_NOTIFICATION_WARNING, 'Please create the advisor record before approving the registration.');
                $cmd = CommandFactory::getCommand('ViewOrganizationApplication');
                $cmd->setApplicationId($app->id);
                $cmd->redirect();
            }

            $app->admin_confirmed = time();
            $app->save();

            PHPWS_Core::initModClass('sdr', 'AdminApproveOrganizationApplicationEmail.php');
            $email = new AdminApproveOrganizationApplicationEmail($app);
            $email->send();

            $cmd = CommandFactory::getCommand('ShowOrganizationApplications');
            $cmd->redirect();
        } else if(UserStatus::isUser()) {
            PHPWS_Core::initModClass('sdr', 'MemberFactory.php');
            PHPWS_Core::initModClass('sdr', 'UserApproveOrganizationApplicationEmail.php');
            $user = MemberFactory::fromLogin();
            if($user->getId() == $app->req_pres_id) {
                $app->pres_confirmed = time();
                $app->checkComplete();
                $app->save();

                $email = new UserApproveOrganizationApplicationEmail($app, $user);
                $email->send();

                $cmd = CommandFactory::getCommand('ShowUserSummary');
                $cmd->redirect();
            } else if($user->getId() == $app->req_advisor_id) {
                $app->advisor_confirmed = time();
                $app->checkComplete();
                $app->save();

                $email = new UserApproveOrganizationApplicationEmail($app, $user);
                $email->send();

                $cmd = CommandFactory::getCommand('ShowUserSummary');
                $cmd->redirect();
            } else {
                PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
                throw new PermissionException('You are not authorized to approve the selected organization registration.');
            }
        } else {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You must be logged in to approve an organization registration.');
        }

        PHPWS_Core::initModClass('sdr', 'exception/UnsupportedFunctionException.php');
        throw new UnsupportedFunctionException('I have no idea what happened but somehow they got to the end of ApproveOrganizationApplicationCommand without either a permission exception or a redirect.');
    }
}

?>
