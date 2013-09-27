<?php

/**
 * SDR Organization Profile
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OrganizationProfile
{
    var $id;
    var $organization_id;
    var $purpose;
    var $club_logo;
    var $meeting_location;
    var $meeting_date;
    var $description;
    var $requirements;
    var $site_url;

    private $_organization;

    public function __construct($id = NULL)
    {
        if(!isset($id)) return;

        $this->id = (int)$id;
        $this->init();
    }

    public static function getByOrganizationId($id)
    {
        $profile = new OrganizationProfile();

        $db = new PHPWS_DB('sdr_organization_profile');
        $db->addWhere('organization_id', $id);
        $result = $db->loadObject($profile);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        $profile->setOrganizationId($id);

        return $profile;
    }

    public function init()
    {
        if(!$this->id) {
            return false;
        }

        $db = new PHPWS_DB('sdr_organization_profile');
        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)) {
            $this->id = -1;
            return $result;
        }
    }

    public function loadOrganization(Organization $org = null)
    {
    	if(!is_null($org)) {
    		if($org->getId() == $this->organization_id) {
    			$this->_organization = $org;
    			return;
    		}
    	}
    	
        PHPWS_Core::initModClass('sdr', 'Organization.php');
        $this->_organization = new Organization($this->organization_id);
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function getOrganizationId()
    {
    	return $this->organization_id;
    }
    
    public function setOrganizationId($id)
    {
    	$this->organization_id = $id;
    }

    public function getPurpose(){
        return $this->purpose;
    }

    public function setPurpose($purpose){
        $this->purpose = $purpose;
    }
    
    public function getClubLogo(){
        return $this->club_logo;
    }
    
    public function setClubLogo($path){
        $this->club_logo = $path;
    }

    public function getMeetingLocation(){
        return $this->meeting_location;
    }

    public function setMeetingLocation($meeting_location){
        $this->meeting_location = $meeting_location;
    }

    public function getMeetingDate(){
        return $this->meeting_date;
    }

    public function setMeetingDate($meeting_date){
        $this->meeting_date = $meeting_date;
    }

    public function getDescription(){
        return $this->description;
    }

    public function setDescription($description){
        $this->description = $description;
    }

    public function getRequirements()
    {
        return $this->requirements;
    }

    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;
    }

    public function getLink()
    {
        $url = $this->site_url;
        if(is_null($url)) return null;
        if(empty($url)) return;

        //Make sure the url begins with 'http://'
        if(preg_match('@^(https?://)@i',$url) == 0){
            $url = 'http://'.$url;
        }

        if(PHPWS_Text::isValidInput($url, 'url')){
            return '<a href="'.$url.'">'.$url.'</a>';
        }
    }

    public function getSiteUrl(){
        return $this->site_url;
    }

    public function setSiteUrl($url)
    {
        $this->site_url = $url;
    }

    public function getOrganization()
    {
    	if(!isset($this->_organization)) {
    		$this->loadOrganization();
    	}
    	
        return $this->_organization;
    }
}

?>
