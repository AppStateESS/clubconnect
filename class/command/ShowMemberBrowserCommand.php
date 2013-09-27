<?php

/**
 * Shows the Administrative Student Search.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowMemberBrowserCommand extends Command
{
    public function allowExecute()
    {
        return UserStatus::hasPermission('person_admin');
    }
	
	function execute(CommandContext $context)
	{
        PHPWS_Core::initModClass('sdr', 'HeaderView.php');
        $header = new HeaderView();
        $header->setTitle('Browse Students and Advisors');

        PHPWS_Core::initModClass('sdr', 'BrowseMembersMenu.php');
        $menu = new BrowseMembersMenu();
        $header->setMenu($menu);

        PHPWS_Core::initModClass('sdr', 'FancyPersonBrowser.php');
        $search = new FancyPersonBrowser();
        $search->setElementId('PersonBrowser');
        $search->setViewCommand(CommandFactory::getCommand('ShowMemberInfo'), 'member_id', 'member_id');
		
		
		$context->setContent($header->show() . $search->show() . '<div id="PersonBrowser"></div>');
	}
}
?>
