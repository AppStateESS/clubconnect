<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'ViewController.php');

class SDR_NullView implements SDR_ViewController
{
    public function render()
    {
        return 'This feature is currently being upgraded.';
    }
}

?>
