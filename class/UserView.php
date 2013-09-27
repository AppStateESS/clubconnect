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
    var $notifications;

    public function addToSidebar($side)
    {
        $this->sidebar[] = $side;
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

        $tpl['NOTIFICATIONS'] = $this->notifications;
        $tpl['MAIN'] = $this->getMain();

        $this->showSDR(PHPWS_Template::process($tpl, 'sdr', 'user.tpl'));
    }
}

?>
