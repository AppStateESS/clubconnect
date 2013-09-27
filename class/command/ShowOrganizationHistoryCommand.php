<?php

/**
 * Shows previous club registrations
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowOrganizationHistoryCommand extends Command
{
    protected $organization_id;

    public function getParams()
    {
        return array('organization_id');
    }

    public function setOrganizationId($id)
    {
        $this->organization_id = $id;
    }

    public function execute(CommandContext $context)
    {
        if(!isset($this->organization_id)) {
            $this->organization_id = $context->get('organization_id');
        }

        $orgid = $this->organization_id;

        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view this organization\'s history.');
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        $orgManager = new OrganizationManager($orgid);

        $context->setContent($orgManager->showHistory());
    }
}
