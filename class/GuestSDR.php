<?php

/**
 * SDR Guest Controller
 * Controls information that Guests have access to.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'SDR.php');

class GuestSDR extends SDR
{
    public function process()
    {
        try {
            parent::process();
        } catch(PermissionException $pe) {
            UserStatus::sendToLogin();
        }

        PHPWS_Core::initModClass('sdr', 'GuestView.php');
        $view = new GuestView();
        $view->setMain($this->context->getContent());
        
        PHPWS_Core::initModClass('sdr', 'SDRNotificationView.php');
        $nv = new SDRNotificationView();
        $nv->popNotifications();
        $view->addNotifications($nv->show());
        
        $view->show();

        $this->saveState();
    }
}

?>
