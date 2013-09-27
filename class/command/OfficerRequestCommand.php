<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'OfficerRequestController.php');
PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class OfficerRequestCommand extends CrudCommand
{
    protected $offreq_id;

    public function __construct()
    {
        $this->ctrl = new OfficerRequestController();
    }

    public function getParams()
    {
        return array('offreq_id');
    }

    public function setOfficerRequestId($id)
    {
        $this->offreq_id = $id;
    }

    public function get(CommandContext $context)
    {
        if($this->offreq_id == 'new') {
            $context->setContent(array(
                'officers' => array()
            ));
            return;
        }

        $officers = $this->ctrl->get($this->offreq_id);
        if(!$officers) {
            header('HTTP/1.1 404 Not Found');
            return;
        }

        $context->setContent($officers[0]);
    }

    public function post(CommandContext $context)
    {
        $pdo = PDOFactory::getInstance();
        $pdo->beginTransaction();

        $offreq = $context->getJsonData();

        $id = $this->ctrl->save($offreq);

        if($id === FALSE) {
            header('HTTP/1.1 500 Internal Server Error');
            $pdo->rollBack();
            return;
        }

        $pdo->commit();

        $this->get($context);
    }
}

?>
