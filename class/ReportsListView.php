<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class ReportsListView extends sdr\View
{
    public static $dir = 'report';
    private $reports;

    public function __construct()
    {
        $this->reports = array();

        $dir = PHPWS_SOURCE_DIR . 'mod/sdr/class/' . self::$dir;

        $files = scandir("{$dir}/");
        foreach($files as $file) {
            $report = preg_replace('/\.php$/', '', $file);
            if($report == $file) continue;
            PHPWS_Core::initModClass('sdr', self::$dir . '/' . $file);
            
            $registration = "{$report}Registration";
            $this->reports[] = new $registration();
        }
    }

    public function show()
    {
        $tpl = array();

        $tpl['TITLE'] = dgettext('sdr', 'ClubConnect Reporting');
        $tpl['TERM'] = Term::getTermSelector();

        foreach($this->reports as $report) {
            $rep = array();
            $rep['NAME'] = $report->getName();

            $cmd = CommandFactory::getCommand('ExecuteReport');
            $cmd->setReport($report->getClass());
            foreach($report->getFormats() as $format) {
                $cmd->setFormat($format);
                $upper = strtoupper($format);
                $rep[$upper] = $cmd->getLink('<i class="icon-cogs"></i> '.$upper, null, 'btn btn-primary');
            }
            $tpl['REPORT'][] = $rep;
        }

        Layout::addPageTitle('Reports');
        return PHPWS_Template::process($tpl, 'sdr', 'ReportsListView.tpl');
    }
}

?>
