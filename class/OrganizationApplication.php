<?php

PHPWS_Core::initModClass('sdr', 'Member.php');

define('OA_PRESIDENT', 1);
define('OA_ADVISOR', 2);
define('OA_OTHER', 0);

class OrganizationApplication
{
    public $id;
    public $term;
    public $name;
    public $address;
    public $election_months;
    public $bank;
    public $ein;

    public $created_on;
    public $updated_on;

    // TODO: change this to organization_id
    public $parent;
    public $_parent;

    public $type;
    public $category;

    public $user_type;
    public $user_id;
    public $_user;

    public $req_pres_id;
    public $_req_pres;

    public $req_advisor_id;
    public $_req_advisor;
    public $req_advisor_name;
    public $req_advisor_dept;
    public $req_advisor_bldg;
    public $req_advisor_phone;
    public $req_advisor_email;

    public $has_website;
    public $wants_website;
    public $website_url;

    public $admin_confirmed;
    public $pres_confirmed;
    public $advisor_confirmed;

    // This gets set after the thing is fully approved.
    public $organization_id;

    public $load_errors;

    public function __construct($id = null)
    {
        if(is_null($id)) {
            return;
        }

        if(is_array($id)) {
            PHPWS_Core::plugObject($this, $id);
            return;
        }

        $this->id = $id;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('sdr_organization_application');
        $db->addJoin('LEFT OUTER', 'sdr_organization_application', 'sdr_organization_type', 'type', 'id');
        $db->addColumn('sdr_organization_application.*');
        $db->addColumn('sdr_organization_type.name', NULL, 'category');
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        $this->election_months = explode(', ', $this->election_months);
        $this->lazyLoad();

        return true;
    }

    public function save()
    {
        // Prepare for Save
        $this->election_months = implode(', ', $this->election_months);

        if(is_null($this->created_on)) $this->created_on = time();
        $this->updated_on = time();

        PHPWS_Core::initCoreClass('Database.php');
        $db = new PHPWS_DB('sdr_organization_application');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('Could not SaveObject');
        }

        return TRUE;
    }

    public function checkComplete()
    {
        if($this->admin_confirmed && $this->pres_confirmed && $this->advisor_confirmed) {
            PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
            PHPWS_Core::initModClass('sdr', 'Organization.php');
            PHPWS_Core::initModClass('sdr', 'OrganizationInstance.php');
            PHPWS_Core::initModClass('sdr', 'Member.php');
            PHPWS_Core::initModClass('sdr', 'Membership.php');
            PHPWS_Core::initModClass('sdr', 'Role.php');

            // Create Organization and Instance
            if(!isset($this->_parent)) {
                $org = new Organization();
                $org->setName($this->name);
                $org->setType($this->type);
                $org->setTerm($this->term);
                $org->setAddress($this->address);
                $org->setBank($this->bank);
                $org->setEin($this->ein);
                $org->setStudentManaged(true);
                $org->rollover_stf = false;
                $org->rollover_fts = true;
                $org->locked = false;
                $org->save();
            } else {
                $inst = clone($this->_parent->getInstance());
                $inst->id = null;
                $inst->term = $this->term;
                $inst->name = $this->name;
                $inst->address = $this->address;
                $inst->bank = $this->bank;
                $inst->ein = $this->ein;
                $inst->type = $this->type;
                $inst->save();

                $org = new Organization($inst->organization_id);
            }

            $this->organization_id = $org->getId();

            $mgr = new OrganizationManager($org);

            // Add Advisor
            $membership = $mgr->addMember($this->_req_advisor, $this->term, 1, 1, false, Role::getAdvisorRole());
            $membership->student_approved_on = $this->advisor_confirmed;
            $membership->setAdministrator(1);
            $membership->save();

            // Add President
            $membership = $mgr->addMember($this->_req_pres, $this->term, 1, 1, false, Role::getPresidentRole());
            $membership->student_approved_on = $this->pres_confirmed;
            $membership->setAdministrator(1);
            $membership->save();

            PHPWS_Core::initModClass('sdr', 'FullyApprovedApplicationEmail.php');
            $email = new FullyApprovedApplicationEmail($this, $org);
            $email->send();

            return TRUE;
        }

        return FALSE;
    }
    
    public function delete()
    {
        if(is_null($this->id) || !isset($this->id)){
            return false;
        }
        
        $db = new PHPWS_DB('sdr_organization_application');
        $db->addWhere('id', $this->id);
        $result = $db->delete();
        
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('Could not SaveObject');
        }

        return TRUE;
    }

    public static function countPendingAdmin()
    {
        $db = new PHPWS_DB('sdr_organization_application');
        $db->addWhere('approved', null);
        $count = $db->count();

        if(PHPWS_Error::logIfError($count)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('Could not count pending Organization Applications for Admin');
        }

        return $count;
    }

    public function loadFromContext(CommandContext $context)
    {

        test($this,1);
        
        $this->lazyLoad();
    }

    public function lazyLoad()
    {
        $this->lazyLoadParent();
        $this->lazyLoadUser();
        $this->lazyLoadReqPres();
        $this->lazyLoadReqAdvisor();
    }

    public function lazyLoadParent()
    {
        if(is_null($this->parent)) return;
        if(!is_null($this->_parent)) {
            if($this->parent == $this->_parent->getId())
                return;
        }

        PHPWS_Core::initModClass('sdr', 'Organization.php');
        $this->_parent = new Organization($this->parent);
    }

    public function lazyLoadUser()
    {
        if(is_null($this->user_id)) return;
        if(!is_null($this->_user)) {
            if($this->user_id == $this->_user->getId())
                return;
        }

        $this->_user = new Member($this->user_id);
    }

    public function lazyLoadReqPres()
    {
        if(is_null($this->req_pres_id)) return;
        if(!is_null($this->_req_pres)) {
            if($this->req_pres_id == $this->_req_pres->getId())
                return;
        }

        $this->_req_pres = new Member($this->req_pres_id);
    }

    public function lazyLoadReqAdvisor()
    {
        if(is_null($this->req_advisor_id)) return;
        if(!is_null($this->_req_advisor)) {
            if($this->req_advisor_id == $this->_req_advisor->getId())
                return;
        }

        $this->_req_advisor = new Member($this->req_advisor_id);
    }

    public function getStringUserType()
    {
        switch($this->user_type) {
        case 0:
            return 'Other';
        case 1:
            return 'President';
        case 2:
            return 'Advisor';
        }

        return null;
    }
}

?>
