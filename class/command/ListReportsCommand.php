<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ListReportsCommand extends Command
{
    public function allowExecute()
    {
        // TODO: Separate permission manager for reports
        // ....or just replace with webfocus?
        return UserStatus::hasPermission('report_annual') ||
               UserStatus::hasPermission('report_greek_gpa') ||
               UserStatus::hasPermission('report_transfer');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'ReportsListView.php');
        $view = new ReportsListView();
        $context->setContent($view->show());
    }
}

?>
