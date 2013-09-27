<?php

/**
 * SDR Main Menu Controller
 * Renders the top navbar, primary navigation
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');
PHPWS_Core::initModClass('sdr', 'Member.php');

class MainMenu extends CommandMenu
{
    protected $admin;

    public function __construct()
    {
        parent::__construct();
    }

    public function setAdminMenu(Menu $menu)
    {
        $this->admin = $menu;
    }

    public function show()
    {
        $tpl = array();
        /*$tpl['MENU'] = parent::show();
        if($this->admin) {
            $tpl['ADMIN'] = $this->admin->show();
        }*/

        $tpl['USER'] = UserStatus::getDisplay();
        return PHPWS_Template::process($tpl, 'sdr', 'MainMenu.tpl');
    }
}

?>
