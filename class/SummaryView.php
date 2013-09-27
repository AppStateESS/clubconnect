<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class SummaryView extends sdr\View
{
    var $main;
    var $side;
    var $term;

    public function __construct($term)
    {
        $this->main = array();
        $this->side = array();

        $this->term = $term;
    }

    public function addMain(sdr\View $view)
    {
        $this->main[] = $view;
    }

    public function addSide(sdr\View $view)
    {
        $this->side[] = $view;
    }

    public function show()
    {
        $tpl = array();

        foreach($this->main as $m) {
            $tpl['MAIN'][]['CONTENT'] = $m->show();
        }

        foreach($this->side as $s) {
            $tpl['SIDE'][]['CONTENT'] = $s->show();
        }

        Layout::addPageTitle("User Summary for " . Term::toString($this->term));

        return PHPWS_Template::process($tpl, 'sdr', 'SummaryView.tpl');
    }
}

?>
