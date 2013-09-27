<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class SetCategoryForOrgAppCommand extends Command
{
    public function getRequestVars()
    {
        $vars = array('action' => 'SetCategoryForOrgApp');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You must be an admin to set the category.');
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');

        $app = new OrganizationApplication($context->get('app_id'));
        $app->type = $context->get('type_id');
        $app->save();

        $context->setContent('true');
    }
}

?>
