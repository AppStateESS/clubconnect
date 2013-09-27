<?php

/**
 * SDR Organization Browser
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'View.php');

class OrganizationBrowserView
{
    public function __construct()
    {
    }

    public function show()
    {
        return javascript('OrganizationBrowser');
    }
}

?>
