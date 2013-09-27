<?php

/**
 * SDR Guest View
 * Shows them a friendly message and then mostly the Organization Browser
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'View.php');

class GuestView extends sdr\SDRView
{
    private $message;
    var $notifications;

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function addNotifications($n)
    {
        $this->notifications = $n;
    }

    public function show()
    {
        $tpl = array();
        $tpl['MAIN'] = $this->getMain();
        $tpl['MESSAGE'] = $this->getMessage();
        $tpl['NOTIFICATIONS'] = $this->notifications;

        $this->showSDR(PHPWS_Template::process($tpl, 'sdr', 'guest.tpl'));
    }
}

?>
