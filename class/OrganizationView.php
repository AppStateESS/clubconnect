<?php

/**
 * SDR Organization View
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'HeaderView.php');
PHPWS_Core::initModClass('sdr', 'Organization.php');
PHPWS_Core::initModClass('sdr', 'OrganizationMenu.php');

class OrganizationView extends HeaderView
{
	private $organization;
    private $main;
	
	public function __construct(Organization $organization)
	{
		$this->organization = $organization;
	}
	
	public function setMain($main)
	{
		$this->main = $main;
	}
	
	public function show()
	{
		$org = $this->organization;

        $this->setTitle($org->getName(false));
        $this->setSubTitle($org->getCategory());
		
		$menu = new OrganizationMenu($org);
        $this->setMenu($menu);

        $warning = '';

        if(!UserStatus::isGuest() && UserStatus::orgAdmin($org->getId())) {
            $this->showTermSelector(true);

            if(!Term::isCurrentTermSelected()) {
                if(UserStatus::isAdmin()) {
                    $warning = 'You are currently working in a historical term.  Any changes made here will be applied to the selected term and students will be notified via the email address we have on record.  To work in the current term, please select it from the dropbox above.';
                } else {
                    $warning = 'You are currently viewing a historical term.  You will not be able to make changes to this roster.  Please contact CSIL to make historical changes.  To work in the current term, please select it from the dropbox above.';
                }
            }
        }

        if(strlen($warning) > 0)
            $warning = '<div class="alert alert-warning">' . $warning . '</div>';

        return parent::show() . $warning . $this->main;
	}
}

?>
