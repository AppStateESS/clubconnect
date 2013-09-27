<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class GetCategoriesCommand extends Command
{
    public function getRequestVars()
    {
        $vars = array('action' => 'GetCategories');

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationType.php');
        $context->setContent(OrganizationType::getOrganizationTypes('all'));
    }
}

?>
