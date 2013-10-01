<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

// Membership types
define('MBR_TYPE_CLUB',         0); // A normal SDR-managed club
define('MBR_TYPE_DC_LIST',      1); // Deans chancellors list
define('MBR_TYPE_SCHOLARSHIP',  2); // A scholarship
define('MBR_TYPE_EMPLOYMENT',   3); // An employment

define('MBR_LEVEL_SUBMEMBER',        0); // Has not yet earned title of member
define('MBR_LEVEL_MEMBER',           1); // A plain jane member
define('MBR_LEVEL_OFFICER',          2); // An officer of the organization
define('MBR_LEVEL_AWAITING_STUDENT', 3); // Request outstanding for the student
define('MBR_LEVEL_AWAITING_ORG',     4); // Request to join outstanding for the organization
define('MBR_LEVEL_ADVISOR',          5); // Advises the organization

class Membership
{
    public $id;
    public $member_id;
    public $organization_id;
    public $term;
    public $student_approved = 0;
    public $student_approved_on;
    public $organization_approved = 0;
    public $organization_approved_on;
    public $hidden = 0;
    public $administrator = 0;
    public $administrative_force = 0;

    public $_organization_name;
    public $_roles; // If we need to show multiple roles for a person
    public $_role_id;
    public $_role_title;

    public $_organization; // Points to an Organization object
    public $_member; // Points to a Member object

    // TODO: Combine all membership types into one table, and store this field in that table
    public $_type; // The membership type. Must be one of the types defined above.

    public function __construct($id = null)
    {
        if(!isset($id)) {
            return;
        }

        $this->_roles = array();
        $this->_actions = array();

        if(is_array($id)) {
            PHPWS_Core::plugObject($this, $id);
            return;
        }

        $this->id = (int)$id;
        $this->init();
    }

    public function init()
    {
        if(!$this->id) {
            return false;
        }

        $db = new PHPWS_DB('sdr_membership');
        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)) {
            $this->id = -1;
            return $result;
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('sdr_membership');

        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    public function delete()
    {
        if(!$this->id || !isset($this->id)){
            throw new InvalidArgumentException('Attempted to delete a Membership that was not initialized with an ID.');
        }

        $db = new PHPWS_DB('sdr_membership_role');
        $db->addWhere('membership_id', $this->id);
        $result = $db->delete();
        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }


        $db = new PHPWS_DB('sdr_membership');
        $db->addWhere('id', $this->id);
        $result = $db->delete();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    public function getLevel()
    {
        if($this->studentApproved() && !$this->organizationApproved())
        return MBR_LEVEL_AWAITING_ORG;
        if(!$this->studentApproved() && $this->organizationApproved())
        return MBR_LEVEL_AWAITING_STUDENT;
        if($this->isOfficer())
        return MBR_LEVEL_OFFICER;
        if($this->isAdvisor())
        return MBR_LEVEL_ADVISOR;

        return MBR_LEVEL_MEMBER;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMemberId(){
        return $this->member_id;
    }

    public function setMemberId($id){
        $this->member_id = $id;
    }

    public function getOrganization()
    {
        return new Organization($this->organization_id, $this->term);
    }

    public function getOrganizationId()
    {
        return $this->organization_id;
    }

    public function setOrganizationId($id){
        $this->organization_id = $id;
    }

    public function getTerm(){
        return $this->term;
    }

    public function setTerm($term){
        $this->term = $term;
    }
    
    public function organizationApproved()
    {
        return $this->organization_approved != 0;
    }
    
    public function setOrganizationApproved($value)
    {
        $this->organization_approved = ($value ? 1 : 0);
    }

    public function getOrganizationApprovedOn()
    {
        return $this->organization_approved_on;
    }

    public function setOrganizationApprovedOn($value)
    {
        $this->organization_approved_on = $value;
    }

    public function studentApproved()
    {
        return $this->student_approved != 0;
    }

    public function setStudentApproved($value)
    {
        $this->student_approved = ($value ? 1 : 0);
    }

    public function getStudentApprovedOn()
    {
        return $this->student_approved_on;
    }

    public function setStudentApprovedOn($value)
    {
        $this->student_approved_on = $value;
    }
    
    public function getLastApproval()
    {
        $sdt = $this->getStudentApprovedOn();
        $org = $this->getOrganizationApprovedOn();

        return $sdt > $org ? $sdt : $org;
    }

    public function getOrganizationName($linkToProfile = true)
    {
        if($linkToProfile){
            PHPWS_Core::initModClass('sdr', 'Organization.php');
            return Organization::getOrganizationProfileLink($this->_organization_name, $this->organization_id);
        }else{
            return $this->_organization_name;
        }
    }

    public function getMemberName(){
        return $this->_member->getFriendlyName();
    }

    public function getMemberUsername(){
        return $this->_member->getUsername();
    }

    public function getRole()
    {
        return $this->_role;
    }

    public function addRole(Role $role)
    {
        $this->_roles[] = $role;
    }

    public function getRoles()
    {
        return $this->_roles;
    }

    public function getRolesConcat($sep = ', ')
    {
        $roles = $this->getRoles();

        if(sizeof($roles) <= 0){
            $role = new Role();
            $role->setTitle('Member');
            $roles[] = $role;
        }

        $rolestrings = array();
        foreach($roles as $role) {
            $rolestrings[] = $role->__toString();
        }

        return implode($sep, $rolestrings);
    }

    public function getYear(){
        return $this->year;
    }

    public function setYear($year){
        $this->year = $year;
    }

    public function getHidden(){
        return $this->hidden != 0;
    }

    public function setHidden($hidden){
        $this->hidden = $hidden;
    }

    public function isAdministrator() {
        return $this->administrator != 0;
    }

    public function setAdministrator($admin) {
        if($admin < 0 || $admin > 1) {
            throw new InvalidArgumentException("Invalid argument passed to Membership::setAdministrator: $admin");
        }
        	
        $this->administrator = $admin;
    }

    public function getAdministrativeForce() {
        return $this->administrative_force;
    }

    public function setAdministrativeForce($force) {
        if($force < 0 || $force > 1) {
            throw new InvalidArgumentException("Invalid argument passed to Membership::setAdministrativeForce: $force");
        }

        $this->administrative_force = $force;
    }

    public function getType(){
        return $this->_type;
    }

    public function setType($type){
        $this->_type = $type;
    }

    public function setOfficer($isOfficer)
    {
        $this->_officer = $isOfficer;
    }

    public function isOfficer()
    {
        if(!isset($this->_officer)) {
            foreach($this->_roles as $role) {
                if($role->isOfficer()) {
                    $this->_officer = TRUE;
                }
            }

            $this->_officer = FALSE;
        }
        	
        return $this->_officer;
    }

    public function setAdvisor($isAdvisor)
    {
        $this->_advisor = $isAdvisor;
    }

    public function isAdvisor()
    {
        if(!isset($this->_advisor)) {
            foreach($this->_roles as $role) {
                if($role->isAdvisor()) {
                    $this->_advisor = TRUE;
                }
            }

            $this->_advisor = FALSE;
        }
        	
        return $this->_advisor;
    }

    public function isConfirmedMember($term=null){
        if(is_null($term))
        $term = Term::getSelectedTerm();

        if(!$this->organizationApproved())
        return false;

        return true;
    }

    public function isAwaitingApproval() {
        return !($this->organizationApproved() && $this->studentApproved());
    }

    public function setMember(Member $m) {
        $this->_member = $m;
    }

    public function getMember() {
        if(isset($this->_member)) {
            $this->_member = new Member($this->getMemberId());
        }
        	
        return $this->_member;
    }

    /************************
     * Static Helper Methods
     */

    public static function isMember($memberId, $organizationId, $term = NULL)
    {
        $db = new PHPWS_DB('sdr_membership');
        $db->addWhere('member_id', $memberId);
        $db->addWhere('organization_id', $organizationId);

        if(is_null($term)){
            PHPWS_Core::initModClass('sdr', 'Term.php');
            $db->addWhere('term', Term::getSelectedTerm());
        }else{
            $db->addWhere('term', $term);
        }

        $result = $db->count();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        if($result > 0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * Creates, initalizes, and saves (to the database) a new Membership object.
     *
     * @param $memberId The memberId for the new membership.
     * @param $organizationId The id of the organization for the new membership.
     * @param $term The term to create this membership for.
     * @param $studentApproved Set to 1 if the student has approved this membership (or if administratively adding a member).
     * @param $organizationApproved Set to 1 if the organization has approved this membership. Set to 0 when a student requests membership.
     * @return Membership Returns the Membership object which was created/saved.
     * @throws CreateMembershipException
     * @throws DatabaseException
     */
    public static function createMembership(Member $member, $organizationId, $term = NULL, $studentApproved, $organizationApproved, $administrative_force = false)
    {
        if(is_null($term)){
            PHPWS_Core::initModClass('sdr', 'Term.php');
            $term = Term::getCurrentTerm();
        }
        
        $memberId = $member->getId();
         
        if(!isset($memberId) || is_null($memberId)){
            PHPWS_Core::initModClass('sdr', 'exception/CreateMembershipException.php');
            throw new CreateMembershipException('Missing member id.', $member);
        }
         
        if(!isset($organizationId) || is_null($organizationId)){
            PHPWS_Core::initModClass('sdr', 'exception/CreateMembershipException.php');
            throw new CreateMembershipException('Missing organization id.', $member);
        }

        // TODO check that org_id exists
        
        // Check to see if the requested membership already exists
        if(Membership::isMember($memberId, $organizationId)){
            PHPWS_Core::initModClass('sdr', 'exception/CreateMembershipException.php');
            throw new CreateMembershipException('Membership already exists.', $member);
        }

        if($studentApproved == 0 && $organizationApproved == 0){
            PHPWS_Core::initModClass('sdr', 'exception/CreateMembershipException.php');
            throw new CreateMembershipException('studentApproved and organizationApproved cannot both be 0.', $member);
        }

        // Create the membership object
        $membership = new Membership();

        $membership->setMemberId($memberId);
        $membership->setOrganizationId($organizationId);
        $membership->setTerm($term);
        if($organizationApproved){
            $membership->setOrganizationApproved(true);
            $membership->setOrganizationApprovedOn(time());
        }
        if($studentApproved){
            $membership->setStudentApproved(true);
            $membership->setStudentApprovedOn(time());
        }
        $membership->setAdministrativeForce($administrative_force);

        $result = $membership->save();

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return $membership;
    }
}

?>
