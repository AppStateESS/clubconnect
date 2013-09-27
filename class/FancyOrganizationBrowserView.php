<?php

PHPWS_Core::initModClass('sdr', 'HeaderView.php');

/**
 * Handles showing the fancy organization viewer with javascript filtering.
 *
 * @author jbooker <jbooker AT tux DOT appstate DOT edu>
 * @package sdr
 */

class FancyOrganizationBrowserView extends sdr\View {

    private $orgBrowser;
    
    public function __construct(FancyOrganizationBrowser $orgBrowser)
    {
        $this->orgBrowser = $orgBrowser;
    }
    
    public function show()
    {
        $headerView = new HeaderView();
        $headerView->setTitle('Club Directory');

        if(!UserStatus::isGuest()) {
            PHPWS_Core::initModClass('sdr', 'BrowseOrganizationsMenu.php');
            $menu = new BrowseOrganizationsMenu();
            $headerView->setMenu($menu);
        }

        $tpl['HEADER'] = $headerView->show();
        
        $this->orgBrowser->setElementId('OrganizationBrowser');
        $tpl['BROWSER'] = $this->orgBrowser->show();

        if(!UserStatus::isAdmin()) {
            $tpl['RED_ASTERISK_MESSAGE'] = 'Clubs marked with a red asterisk (<span style="color: #F00">*</span>) are not yet registered for this term.  You will be unable to request membership until the club has registered with CSIL.  Please continue to check this page for updates to club status.';
        }
        
        return PHPWS_Template::process($tpl, 'sdr', 'OrganizationBrowser.tpl');
    }
}

?>
