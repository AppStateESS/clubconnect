<?php

/**
 * SDR Organization Profile Controller
 * Manages the displaying and editing of club profiles.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

// TODO: Bring back in West's validation code

class OrganizationProfileController
{
    private $view;
    private $organizationProfile;

    public function __construct(OrganizationProfile $profile = NULL)
    {
    	if(!is_null($profile)) {
    		$this->setOrganizationProfile($profile);
    	}
    }

    public function setOrganizationProfile(OrganizationProfile $profile)
    {
        $this->organizationProfile = $profile;
    }

    public function view()
    {
        if(!is_a($this->organizationProfile, 'OrganizationProfile')) {
            PHPWS_Core::initModClass('sdr', 'exception/SDRException.php');
            throw new SDRException('Did not provide an OrganizationProfile to edit');
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationProfileView.php');
        $view = new OrganizationProfileView($this->organizationProfile);

        if(UserStatus::orgAdmin($this->organizationProfile->getOrganizationId())) {
            $view->setEditCommand(CommandFactory::getCommand('EditOrganizationProfile'));
        }
        
        return $view->show();
    }

    /*
     * edit
     *
     * Takes an optional array parameter of errors, which is an associative
     * array of reasons why the input is invalid paired with the name of the
     * input field.
     *
     * @param errors mixed Array of errors or null if none
     * @return html
     */
    public function edit()
    {
        if(!is_a($this->organizationProfile, 'OrganizationProfile')) {
            PHPWS_Core::initModClass('sdr', 'exception/SDRException.php');
        	throw new SDRException('Did not provide an OrganizationProfile to edit');
        }
        
        if(!UserStatus::orgAdmin($this->organizationProfile->getOrganizationId())) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
        	throw new PermissionException('You do not have permissiohn to edit this Organization Profile.');
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationProfileEdit.php');
        $view = new OrganizationProfileEdit($this->organizationProfile);
        return $view->show();
    }

    public function save()
    {
    	if(!is_a($this->organizationProfile, 'OrganizationProfile')) {
    		PHPWS_Core::initModClass('sdr', 'exception/SDRException.php');
    		throw new SDRException('Did not provide an OrganizationProfile to save');
    	}
    	
    	if(!UserStatus::orgAdmin($this->organizationProfile->getOrganizationId())) {
    		PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
    		throw new PermissionException('You do not have permission to save this Organization Profile.');
    	}
        
        $profile = $this->organizationProfile;
    	
    	$db = new PHPWS_DB('sdr_organization_profile');
    	$result = $db->saveObject($profile);

    	if(PHPWS_Error::logIfError($result)) {
    		PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
    		throw new DatabaseException('Could not SaveObject');
    	}
    	
    	return TRUE;
    }
}

?>
