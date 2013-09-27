<?php

/**
 * SDR Organization Instance Model
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OrganizationInstance
{
    public $id;
    public $organization_id;
    public $term;
    public $name;
    public $shortname;
    public $address;
    public $bank;
    public $ein;
    public $type;

    public function __construct($id = NULL)
    {
        if(!isset($id)) return;
        $this->id = (int)$id;
        $this->init();
    }

    public function init()
    {
        if(!$this->id) return false;

        $db = new PHPWS_DB('sdr_organization_instance');
        SDR::throwDb($db->loadObject($this));
    }

    public function save()
    {
        $db = new PHPWS_DB('sdr_organization_instance');
        SDR::throwDb($db->saveObject($this));
    }

    public function delete()
    {
        $db = new PHPWS_DB('sdr_organization_instance');
        $db->addWhere('id', $this->id);
        SDR::throwDb($db->delete());
    }

    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; }

    public function getOrganizationId() { return $this->organization_id; }
    public function setOrganizationId($id) { $this->organization_id = $id; }

    public function getTerm() { return $this->term; }
    public function setTerm($term) { $this->term = $term; }

    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; }

    public function getShortName() { return $this->shortname; }
    public function setShortName($sn) { $this->shortname = $sn; }

    public function getAddress() { return $this->address; }
    public function setAddress($address) { $this->address = $address; }

    public function getBank() { return $this->bank; }
    public function setBank($bank) { $this->bank = $bank; }

    public function getEin() { return $this->ein; }
    public function setEin($ein) { $this->ein = $ein; }

    public function getType() { return $this->type; }
    public function setType($type) { $this->type = $type; }
}

?>
