<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');

class DeleteOrganizationApplicationCommand extends Command
{
    private $app_id;

    public function getRequestVars()
    {
        $vars = array('action' => 'DeleteOrganizationApplication');

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
        $appId = $context->get('app_id');
        
        if(is_null($appId)){
            throw new InvalidArgumentException('Missing application id.');
        }
        
        # Load the application
        $app = new OrganizationApplication($appId);

        # Delete the application
        $app->delete();

        PHPWS_Core::initModClass('sdr', 'AdminDeleteOrganizationApplicationEmail.php');
        $email = new AdminDeleteOrganizationApplicationEmail($app);
        $email->send();
        
        # Return the user to the applications view
        NQ::simple('sdr', SDR_NOTIFICATION_SUCCESS, 'Deleted organization registration.');
        $successCmd = CommandFactory::getCommand('ShowOrganizationApplications');
        $successCmd->redirect();
    }
}

?>
