<?php

/**
 * SDR Organization Roster Controller
 *
 * Provides all functions for managing an organization's roster, including
 * stuff necessary for the ajax magic.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OrganizationRoster
{
	private $organization;
	
	public function __construct(Organization $org)
	{
        $this->organization = $org;
	}
	
	public function show()
	{
		$term = Term::getSelectedTerm();
		
		PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
		$memberships = MembershipFactory::getMembershipsByOrganization(
	        $this->organization->getId(), $term);

	    PHPWS_Core::initModClass('sdr', 'OrganizationRosterView.php');
	    $view = new OrganizationRosterView($this->organization, $memberships, $term);
	    
	    return $view->show();
	}
}
