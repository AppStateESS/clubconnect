<?php

/**
 * SDR User View
 * All Non-Admin Authenticated Users.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'View.php');

class UserView extends sdr\SDRView
{
    var $sidebar = array();
    var $toolbar = array();
    var $notifications;

    public function addToSidebar($side)
    {
        $this->sidebar[] = $side;
    }

    public function addToToolbar($name, $menu)
    {
        $this->toolbar[$name] = $menu;
    }
    
    public function addNotifications($n)
    {
    	$this->notifications = $n;
    }

    public function show()
    {
        $tpl = array();

        foreach($this->sidebar as $side) {
            $tpl['SIDEBAR'][]['SIDE_ITEM'] = $side;
        }

        foreach($this->toolbar as $name => $menu) {
            $tpl['TOOLBAR'][] = array(
                'DROPDOWN_TITLE' => $name,
                'DROPDOWN_CONTENT' => $menu
            );
        }

        $tpl['USER'] = UserStatus::getDisplay();

        $tpl['NOTIFICATIONS'] = $this->notifications;
        $tpl['MAIN'] = $this->getMain();

        $this->showSDR(PHPWS_Template::process($tpl, 'sdr', 'user.tpl'));
    }
}

?>
