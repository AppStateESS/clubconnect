<?php

/**
 * Shows the Administrative Summary
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowAdminSummaryCommand extends Command
{
	function execute(CommandContext $context)
	{
        $vars['SOURCE_HTTP'] = PHPWS_SOURCE_HTTP;
        $vars['SDR_HTTP'] = PHPWS_SOURCE_HTTP . 'sdr/';

        $context->setContent(
            PHPWS_Template::process($vars, 'sdr', 'AdminSummary.tpl'));
	}
}
