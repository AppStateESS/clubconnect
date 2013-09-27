<?php

/**
 * SDR Advisor Class
 * 
 * Every student and advisor can be a member of an organization and can
 * administer an organization.  We treat Advisors as a certain type of
 * Member.
 * 
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */


class Advisor
{
    public $id = null;
	public $home_phone = null;
	public $office_phone = null;
	public $cell_phone = null;
	public $office_location = null;
	public $department = null;

    public function __construct($id = null)
    {
        if(is_null($id)) return;

        $this->id = $id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('sdr_advisor');
        if(!SDR::throwDb($db->loadObject($this)))
            $this->id = -1;
    }

    public function save()
    {
        $db = new PHPWS_DB('sdr_advisor');
        SDR::throwDb($db->saveObject($this, false, false));
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }
	
	public function setHomePhone($home_phone)
	{
		$this->home_phone = $home_phone;
	}
	
	public function getHomePhone()
	{
		return $this->home_phone;
	}
	
	public function setOfficePhone($office_phone)
	{
		$this->office_phone = $office_phone;
	}
	
	public function getOfficePhone()
	{
		return $this->office_phone;
	}
	
	public function setCellPhone($cell_phone)
	{
		$this->cell_phone = $cell_phone;
	}
	
	public function getCellPhone()
	{
		return $this->cell_phone;
	}
	
	public function setOfficeLocation($office_location)
	{
		$this->office_location = $office_location;
	}
	
	public function getOfficeLocation()
	{
		return $this->office_location;
	}
	
	public function setDepartment($department)
	{
		$this->department = $department;
	}
	
	public function getDepartment()
	{
		return $this->department;
	}
}
