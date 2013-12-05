<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');
PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
PHPWS_Core::initModClass('sdr', 'Member.php');

class OrganizationApplicationMenu extends CommandMenu
{
    protected function setupCommands()
    {
        $member = new Member(NULL, UserStatus::getUsername());

        if($app->admin_confirmed && (
                ($app->req_pres_id    == $member->getId() && is_null($app->pres_confirmed)) ||
                ($app->req_advisor_id == $member->getId() && is_null($app->advisor_confirmed)))) {
            $approve = CommandFactory::getCommand('ApproveOrganizationApplication');
            $approve->setApplicationId($app->id);
            $this->addCommand('Approve Affiliation', $approve);
            $deny = CommandFactory::getCommand('DenyOrganizationApplication');
            $deny->setApplicationId($app->id);
            $this->addCommand('Deny Affiliation', $deny);
        }
    }
}

?>
