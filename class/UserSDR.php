<?php

/**
 * SDR User Controller
 * Controls the interface for authenticated non-admin users.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'SDR.php');

class UserSDR extends SDR
{
    public function process()
    {
        try {
            parent::process();
        } catch(PermissionException $pe) {
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Unauthorized: ' . $pe->getMessage());
            PHPWS_Core::log('PERMISSION EXCPETION\n' . $pe->getMessage(), 'sdr_exception.log');
            CommandContext::getInstance()->errorBack();
        }

        PHPWS_Core::initModClass('sdr', 'UserView.php');
        $view = new UserView();
        $view->setMain($this->context->getContent());

        if(UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'AdminMenu.php');
            $menu = new AdminMenu();
            $menu->setContext($this->context);
            $view->addToSidebar($menu->show());
        } else if(UserStatus::isUser()) {
            PHPWS_Core::initModClass('sdr', 'UserMenu.php');
            $menu = new UserMenu();
            $menu->setContext($this->context);
            $view->addToSidebar($menu->show());
        }
        
        PHPWS_Core::initModClass('sdr', 'SDRNotificationView.php');
        $nv = new SDRNotificationView();
        $nv->popNotifications();
        $view->addNotifications($nv->show());

        PHPWS_Core::initModClass('sdr', 'PersistentAdminMenu.php');
        $menu = new PersistentAdminMenu();
        $menu->setContext($this->context);
        $view->addToToolbar('Administration', $menu->show());
        
        $view->show();

        $this->saveState();
    }
}
?>
