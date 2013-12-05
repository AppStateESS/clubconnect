<?php

namespace sdr;

/**
 * SDR View
 * Handles the very basic SDR view.  This has a top-bar to show login status
 * and/or term-awareness, and then whatever child view is appropriate to the
 * user's status (determined by the SDR contoller).
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class View
{
    public abstract function show();
}

abstract class SDRView extends View
{
    private $main;

    public function setMain($content)
    {
        $this->main = $content;
    }

    public function getMain()
    {
        return $this->main;
    }

    public function showSDR($content)
    {
        $tpl = array();
        $tpl['MAIN'] = $content;
        $tpl['USER'] = \UserStatus::getDisplay();

        \PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
        \PHPWS_Core::initModClass('sdr', 'UserStatus.php');
        if(\GlobalLock::isLocked() && \UserStatus::isUser()){
            \NQ::Simple('sdr', SDR_NOTIFICATION_WARNING, \GlobalLock::getMessage());
        }

        \Layout::addStyle('sdr', 'style.css');
        \Layout::add(\PHPWS_Template::process($tpl, 'sdr', 'sdr.tpl'));
    }
}

?>
