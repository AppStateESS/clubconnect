<?php

/**
 * SDR Admin Controller
 * Controls the interface for authenticated administrators.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'SDR.php');

class AdminSDR extends SDR
{
    public function process()
    {
        parent::process();

        PHPWS_Core::initModClass('sdr', 'UserView.php');
        $view = new UserView();
        $view->setMain($this->context->getContent());
        
        PHPWS_Core::initModClass('sdr', 'AdminMenu.php');
        $menu = new AdminMenu();
        $menu->setContext($this->context);
        $view->addToSidebar($menu->show());

        PHPWS_Core::initModClass('sdr', 'SDRNotificationView.php');
        $nv = new SDRNotificationView();
        $nv->popNotifications();
        $view->addNotifications($nv->show());
        
        $view->show();

        $this->saveState();
    }
}

?>
