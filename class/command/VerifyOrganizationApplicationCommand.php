<?php

/**
 * Verifies the Organization Application, returning the Review page.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class VerifyOrganizationApplicationCommand extends Command
{
    function getRequestVars()
    {
        return array('action' => 'VerifyOrganizationApplication');
    }

    function execute(CommandContext $context)
    {
        // Is this appropriate?
        $context->addParam('term', Term::getCurrentTerm());

        PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
        PHPWS_Core::initModClass('sdr', 'OrganizationApplicationForm.php');
        PHPWS_Core::initModClass('sdr', 'OrganizationApplicationView.php');

        $app = new OrganizationApplication();
        $form = new OrganizationApplicationForm($app);
        $form->post($context);

        $view = new OrganizationApplicationView($app);
        $view->errors = $form->postErrors;

        // I kind of feel like this is doing it wrong.
        $context->setContent(array('errors' => count($form->postErrors), 'view' => $view->show()));
    }
}

?>
