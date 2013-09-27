<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ExecuteReportCommand extends Command {

    protected $report;
    protected $format;

    public function getParams()
    {
        return array('report', 'format');
    }

    public function setReport($report)
    {
        $this->report = $report;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    function execute(CommandContext $context)
    {
        if(!isset($this->report)) {
            $this->report = $context->get('report');
        }
        $report = $this->report;

        if(!isset($this->format)) {
            $this->format = $context->get('format');
        }
        $format = $this->format;

        PHPWS_Core::initModClass('sdr','report/'.$report.'.php');
        $rep = new $report(Term::getSelectedTerm());

        switch(strtoupper($format)) {
        case 'HTML':
            $context->setContent($rep->renderHTML());
            return;
        case 'PDF':
            $context->setContent($rep->renderPDF());
            return;
        }
    }
}

?>
