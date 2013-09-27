<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class ClubRegistrationListView extends \sdr\View
{
    protected $regs;

    public function __construct(array $regs)
    {
        $this->regs = $regs;
    }

    public function show()
    {
        $tpl = array();

        $tpl['HEAD_NAME']             = dgettext('sdr', 'Club Name');
        $tpl['HEAD_DATE']             = dgettext('sdr', 'Date Registered');
        $tpl['HEAD_ADMIN_APPROVED']   = dgettext('sdr', 'CSIL Approved');
        $tpl['HEAD_PRES_APPROVED']    = dgettext('sdr', 'President Approved');
        $tpl['HEAD_ADVISOR_APPROVED'] = dgettext('sdr', 'Advisor Approved');
        $tpl['HEAD_ACTIONS']          = dgettext('sdr', 'Actions');

        if(empty($this->apps)) {
            $tpl['NO_RESULTS'] = dgettext('sdr', 'No club registrations are pending.');
        } else foreach($this->apps as $app) {

            $actions = array();
            
            if(!$app->admin_confirmed) {
                $cmd = CommandFactory::getCommand('ViewOrganizationApplication');
                $cmd->setApplicationId($app->id);
                $action = $cmd->getLink('Process');
            } else {
                $cmd = CommandFactory::getCommand('RemindOrganizationApplication');
                $cmd->setApplicationId($app->id);
                $action = $cmd->getLink('Send Reminder');
                $cmd = CommandFactory::getCommand('ViewOrganizationApplication');
                $cmd->setApplicationId($app->id);
                $action .= ' | ' . $cmd->getLink('View');
            }
            
            $delCmd = CommandFactory::getCommand('DeleteOrganizationApplication');

            $row = array();

            $row['ADMIN_WARNING']   = $app->admin_confirmed ? 'success' : 'warning';
            $row['PRES_WARNING']    = $app->pres_confirmed ? 'success' : 'warning';
            $row['ADVISOR_WARNING'] = $app->advisor_confirmed ? 'success' : 'warning';
            $row['NAME']             = $app->name;
            $row['DATE']             = date('d M Y', $app->created_on);
            $row['ADMIN_APPROVED']   = $app->admin_confirmed   ? date('d M Y', $app->admin_confirmed) : 'No';
            $row['PRES_APPROVED']    = $app->pres_confirmed    ? date('d M Y', $app->pres_confirmed) : 'No';
            $row['ADVISOR_APPROVED'] = $app->advisor_confirmed ? date('d M Y', $app->advisor_confirmed) : 'No';
            $row['ACTIONS']          = $action;

            $tpl['ROW'][] = $row;
        }

        return PHPWS_Template::process($tpl, 'sdr', 'AdminOrganizationApplicationsView.tpl');
    }
}

?>
