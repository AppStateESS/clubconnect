<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class RemindOrganizationApplicationCommand extends Command
{
    private $app_id;

    public function setApplicationId($app)
    {
        $this->app_id = $app;
    }

    public function getRequestVars()
    {
        $vars = array('action' => 'RemindOrganizationApplication');

        if(!is_null($this->app_id)) {
            $vars['app_id'] = $this->app_id;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to send reminders.');
        }

        $this->app_id = $context->get('app_id');
        if(is_null($this->app_id)) {
            PHPWS_Core::initModClass('sdr', 'InvalidArgumentException.php');
            throw new InvalidArgumentException('Please specify an application ID.');
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
        PHPWS_Core::initModClass('sdr', 'OrganizationApplicationReminderEmail.php');

        $cmd = CommandFactory::GetCommand('ShowOrganizationApplications');

        $app = new OrganizationApplication($this->app_id);

        if(!$app->admin_confirmed) {
            NQ::simple('sdr', SDR_NOTIFICATION_ERROR, 'You must first approve this application before you can bug presidents and advisors about it!');
            $cmd->redirect();
        }

        $email = new OrganizationApplicationReminderEmail($app);
        $email->send();
        $recipients = implode('<br />', $email->getRecipients());

        NQ::simple('sdr', SDR_NOTIFICATION_SUCCESS, 'Reminders have been sent to:<br />' . $recipients);
        $cmd->redirect();
    }
}

?>
