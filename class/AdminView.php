<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'View.php');

class AdminView extends sdr\SDRView
{
    var $tabs;

    public function setTabs($t)
    {
    	$this->tabs = $t;
    }

    public function show()
    {
    	$tpl = array();
    	
    	$tpl['TABS'] = $this->tabs;
    	$tpl['MAIN'] = $this->getMain();
    	
    	$this->showSDR(PHPWS_Template::process($tpl, 'sdr', 'admin.tpl'));
    }
}

?>
