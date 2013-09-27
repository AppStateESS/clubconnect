<?php

/**
 * SDR Organization Profile AJAX Viewer
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'ViewController.php');

class SDR_OrganizationProfileAjax implements SDR_ViewController
{
    public function __construct()
    {
    }

    public function getJsCallback()
    {
        return 'sdrOrganizationProfile';
    }

    public function render()
    {
        return javascript('modules/sdr/OrganizationProfile');
    }
}

?>
