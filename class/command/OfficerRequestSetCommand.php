<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'OfficerRequestController.php');
PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class OfficerRequestSetCommand extends CrudCommand
{
    public function __construct()
    {
        $this->ctrl = new OfficerRequestController();
    }

    public function get(CommandContext $context)
    {
        $officers = $this->ctrl->get();
        if(!count($officers)) {
            header('HTTP/1.1 404 Not Found');
            return;
        }

        $context->setContent($officers[0]);
    }

    public function post(CommandContext $context)
    {
        $offreq = $context->getJsonData();

        $id = $this->ctrl->create($offreq);

        if($id === FALSE) {
            header('HTTP/1.1 500 Internal Server Error');
            return;
        }

        $cmd = new OfficerRequestCommand();
        $cmd->setOfficerRequestId($id);
        $cmd->get($context);
    }
}

?>
