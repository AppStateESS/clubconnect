<?php

/**
 * SDR Header View - displays a pretty yellow box with a title, subtitle,
 * menu, and term selector.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');

class HeaderView extends sdr\View
{
    private $title;
    private $subtitle;
    private $menu;
    private $showTermSelector;

    public function __construct()
    {
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setSubTitle($subtitle)
    {
        $this->subtitle = $subtitle;
    }

    public function setMenu(CommandMenu $menu)
    {
        $this->menu = $menu;
    }

    public function showTermSelector($show = TRUE)
    {
        $this->showTermSelector = $show;
    }

    public function show()
    {
        $tpl = array();

        if(!is_null($this->title)) {
            $tpl['TITLE'] = $this->title;
            Layout::addPageTitle($this->title);
        }

        if(!is_null($this->subtitle))
            $tpl['SUBTITLE'] = $this->subtitle;

        if(!is_null($this->menu))
            $tpl['MENU'] = $this->menu->show();

        if($this->showTermSelector)
            $tpl['TERM'] = Term::getTermSelector();

        return PHPWS_Template::process($tpl, 'sdr', 'HeaderView.tpl');
    }
}

?>
