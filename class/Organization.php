<?php

/**
 * SDR Organization Model
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class Organization
{
    public $id;
    public $banner_id;
    public $locked;
    public $reason_access_denied;
    public $rollover_stf;
    public $rollover_fts;
    public $student_managed;
    public $agreement;
    public $instance_id;
    public $term;
    public $name;
    public $shortname;
    public $address;
    public $bank;
    public $ein;
    public $type_id;
    public $category;

    public function __construct($id = NULL, $term = NULL)
    {
        if(!isset($id)) return;

        $this->id = (int)$id;

        $this->term = $term;
        if(is_null($this->term)) {
            $this->term = Term::getSelectedTerm();
        }

        $this->init();
    }

    public function init()
    {
        if(!$this->id) {
            return false;
        }

        // Try to load by per-instance term, but if that doesn't work, fall though.
        if(!is_null($this->term)) {
            $db = new PHPWS_DB('sdr_organization_full');
            $db->addWhere('id', $this->id);
            $db->addWhere('term', $this->term);
            SDR::throwDb($db->loadObject($this));
            if(!is_null($this->instance_id)) return;
        }

        // Grab whatever the most recent instance is.
        $db = new PHPWS_DB('sdr_organization_recent');
        SDR::throwDb($db->loadObject($this));
    }
    
    public function save()
    {
        $org_db = new PHPWS_DB('sdr_organization');
        $org_db->addValue('banner_id', $this->banner_id);
        $org_db->addValue('locked', $this->locked);
        $org_db->addValue('reason_access_denied', $this->reason_access_denied);
        $org_db->addValue('rollover_stf', $this->rollover_stf);
        $org_db->addValue('rollover_fts', $this->rollover_fts);
        $org_db->addValue('student_managed', $this->student_managed);
        $org_db->addValue('agreement', $this->agreement);

        $inst_db = new PHPWS_DB('sdr_organization_instance');
        $inst_db->addValue('name', $this->name);
        $inst_db->addValue('shortname', $this->shortname);
        $inst_db->addValue('address', $this->address);
        $inst_db->addValue('bank', $this->bank);
        $inst_db->addValue('type', $this->type_id);
        $inst_db->addValue('ein', $this->ein);

        if($this->id) {
            $org_db->addWhere('id', $this->id);
            $inst_db->addWhere('id', $this->instance_id);
            $inst_db->addWhere('term', $this->term);
            SDR::throwDb($org_db->update());
            SDR::throwDb($inst_db->update());
        } else {
            $this->id = SDR::throwDb($org_db->insert());
            $inst_db->addValue('organization_id', $this->id);
            SDR::throwDb($inst_db->insert());
        }
    }

    public function getInstance($term = NULL)
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationInstance.php');
        $instance = new OrganizationInstance();

        if(!is_null($term) && $term != $this->term) {
            $db = new PHPWS_DB('sdr_organization_instance');
            $db->addWhere('organization_id', $this->id);
            $db->addWhere('term', $term);
            SDR::throwDb($db->loadObject($instance));
            return $instance;
        }

        $instance->setId($this->instance_id);
        $instance->setOrganizationId($this->id);
        $instance->setTerm($this->term);
        $instance->setName($this->name);
        $instance->setAddress($this->address);
        $instance->setBank($this->bank);
        $instance->setEin($this->ein);
        $instance->setType($this->type_id);
        $instance->setShortName($this->shortname);

        return $instance;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function getMembership($user, $term=null){
        if(!is_null($term))
            $term = Term::getCurrentTerm();
            
        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        return MembershipFactory::getUserMembershipByOrganization($this->id, $user, $term);
    }
    
    /* Getter/Setter methods */
    
    public function getId()
    {
    	return $this->id;
    }

    public function getName($linkToProfile = true){
        if($linkToProfile){
            return Organization::getOrganizationProfileLink($this->name, $this->id);
        }else{
            return $this->name;
        }
    }

    public function setName($name){
        $this->name = $name;
    }

    public function getShortName() {
        return $this->shortname;
    }

    public function setShortName($shortname) {
        $this->shortname = $shortname;
    }

    public function getType(){
        return $this->type_id;
    }

    public function setType($type){
        $this->type_id = $type;
    }

    public function getLocked(){
        return $this->locked;
    }

    public function setLocked($locked){
        $this->locked = $locked;
    }

    public function getReasonAccessDenied(){
        return $this->reason_access_denied;
    }

    public function setReasonAccessDenied($reason_access_denied){
        $this->reason_access_denied = $reason_access_denied;
    }

    public function getAgreement() {
        return $this->agreement;
    }

    public function setAgreement($agreement) {
        $this->agreement = $agreement;
    }

    public function registeredForTerm($term)
    {
        $db = new PHPWS_DB('sdr_organization_instance');
        $db->addWhere('organization_id', $this->id);
        $db->addWhere('term', $term);
        return !!SDR::throwDb($db->count());
    }
    
    public function isGreek()
    {
        // TODO: omfg this is horrible
        return $this->type_id == 11 || $this->type_id == 12;
    }

    public function getAddress() { return $this->address; }

    public function setAddress($address) { $this->address = $address; }

    public function getBank() { return $this->bank; }

    public function setBank($bank) { $this->bank = $bank; }

    public function getEin() { return $this->ein; }

    public function setEin($ein) { $this->ein = $ein; }

    public function getTerm() { return $this->term; }

    public function setTerm($term) { $this->term = $term; }

    public function getStudentManaged() { return $this->student_managed; }

    public function setStudentManaged($managed) { $this->student_managed = $managed; }

    /*************************
     * Static helper methods *
     */
    
    /**
     * Returns an associative array of all possible organizations. Useful for drop down lists.
     * @return Array
     */
    public static function getOrganizationList($addDefaultOption = TRUE)
    {
        $db = new PHPWS_DB('sdr_organization_full');
        $db->addOrder('name ASC');
        $result = $db->getObjects('Organization');
        
        $orgs = array();
        if($addDefaultOption){
            $orgs[0] = 'Choose organization...';
        }
        
        foreach($result as $org){
            $orgs[$org->getId()] = $org->getName();
        }
        
        return $orgs;
    }
    
    /**
     * Returns true if an organization with the same name already exists (not case sensitive),
     * false if the given org name does not exist.
     * @return boolean
     */
    public static function organizationExistsByName($name)
    {
        $db = new PHPWS_DB('sdr_organization_instance');
        $db->addWhere('name', $name, 'ILIKE');
        return !!SDR::throwDb($db->count());
    }
    
    public static function getOrganizationProfileLink($orgName, $orgId){
        $profileCmd = CommandFactory::getCommand('ShowOrganizationProfile');
        $profileCmd->setOrganizationId($orgId);
        return PHPWS_Text::moduleLink($orgName, 'sdr', $profileCmd->getRequestVars());
    }
}

?>
