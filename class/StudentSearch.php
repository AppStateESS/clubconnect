<?php

/**
 * Student Search - Controller for the student search interface
 */

class StudentSearch {
    
    private $studentSelectedCmd;
    private $searchField;
    private $limitToOrganizationId;
    private $termLimitMin;
    private $termLimitMax;
    private $searchType = 'all';
    
    private $organizationId; // The organization id  to pass to the "selected command", if set
    
    private $db;
    
    public function setStudentSelectedCommand(Command $studentCmd)
    {
        $this->studentSelectedCmd = $studentCmd;
    }
    
    public function setSearchField($value)
    {
        $this->searchField = $value;
    }
    public function limitToOrganization($id)
    {
        $this->limitToOrganizationId = $id;
    }
    
    public function setTermLimitMin($term){
        $this->termLimitMin = $term;
    }
    
    public function setTermLimitMax($term){
        $this->termLimitMax = $term;
    }
    
    public function setOrganizationId($id){
        $this->organizationId = $id;
    }

    // TODO: do this right
    public function setSearchType($type) {
        $type = strtolower($type);
        if(in_array($type, array('all', 'student', 'advisor')))
            $this->searchType = $type;
    }
    
    public function getResultsView()
    {
        $resultsView = new StudentSearchResultsView($this->studentSelectedCmd);
        $resultsView->pager->db = $this->db;
        return $resultsView;
    }

    public function getDb()
    {
        return $this->db;
    }
    
    public function doSearch()
    {
        PHPWS_Core::initModClass('sdr', 'StudentSearchResultsView.php');
        
        // Set the target organization ID on the selected student command
        if(isset($this->organizationId)){
            $this->studentSelectedCmd->setOrganizationId($this->organizationId);
        }

        $db = new PHPWS_DB('sdr_member');

        $searchParts = explode(' ', trim($this->searchField));
        
        if(sizeof($searchParts) > 1){
            // Looks like a name, so search on any part of any word being any part of a name
            $db->addWhere('sdr_member.first_name', '%' . $searchParts[0] . '%', 'ILIKE', 'OR', 'firstGrp');
            $db->addWhere('sdr_member.middle_name', '%' . $searchParts[0] . '%', 'ILIKE', 'OR', 'firstGrp');
            $db->addWhere('sdr_member.last_name', '%' . $searchParts[0] . '%', 'ILIKE', 'OR', 'firstGrp');
            $db->setGroupConj('firstGrp', 'OR');
            
            $db->addWhere('sdr_member.first_name', '%' . $searchParts[1] . '%', 'ILIKE', 'OR', 'secondGrp');
            $db->addWhere('sdr_member.middle_name', '%' . $searchParts[1] . '%', 'ILIKE', 'OR', 'secondGrp');
            $db->addWhere('sdr_member.last_name', '%' . $searchParts[1] . '%', 'ILIKE', 'OR', 'secondGrp');
            $db->setGroupConj('secondGrp', 'AND');
            
        }else{
            // One "word", could be a single name, username, or banner ID
            $searchParts[0] = trim($searchParts[0]);
            if(preg_match("/^[0-9]{9}$/",$searchParts[0])){
                // 9-digit, all numberic -- Looks like a banner ID, just search on student_id
                $db->addWhere('sdr_member.id', $searchParts[0], '=');
            }else if(preg_match("/.*@(email\.)?appstate\.edu/",$searchParts[0])){
                // Looks like the user entered an email address, just search on user names
                $email = preg_replace("/@appstate.edu||@email.appstate.edu/",'',$searchParts);
                $db->addWhere('sdr_member.username', '%' . $email[0] . '%', 'ILIKE');
            }else{
                // Could be a name or username
                $db->addWhere('sdr_member.first_name', '%' . $searchParts[0] . '%', 'ILIKE', 'OR', 'firstGrp');
                $db->addWhere('sdr_member.middle_name', '%' . $searchParts[0] . '%', 'ILIKE', 'OR', 'firstGrp');
                $db->addWhere('sdr_member.last_name', '%' . $searchParts[0] . '%', 'ILIKE', 'OR', 'firstGrp');
                $db->setGroupConj('firstGrp', 'OR');
            
                $db->addWhere('sdr_member.username', '%' . $searchParts[0] . '%', 'ILIKE', 'OR', 'firstGrp');
                //$this->resultsView->pager->db->setGroupConj('thirdGrp', 'OR');
            }
        }
        
        // Handle the organization limit
        if(isset($this->limitToOrganizationId) && $this->limitToOrganizationId != 0){
        	if(UserStatus::orgAdmin($this->limitToOrganizationId)) {
                $db->addJoin('RIGHT', 'sdr_member', 'sdr_membership', 'id', 'member_id');
                $db->addWhere('sdr_membership.organization_id', $this->limitToOrganizationId, '=', 'AND', 'fourthGroup');
                $db->setGroupConj('fourthGroup', 'AND');
        	} else {
        		PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
        		throw new PermissionException('You do not have permission to view memberships for the selected organization.');
        	}
        }
        
        // Handle the term limit
        if((isset($this->termLimitMin) && !empty($this->termLimitMin)) || (isset($this->termLimitMax) && !empty($this->termLimitMax))){
            $db->addJoin('RIGHT', 'sdr_member', 'sdr_membership', 'id', 'member_id');
            
            // If they're equal we only need one where statement with an equal operator
            if($this->termLimitMin == $this->termLimitMax){
                $db->addWhere('sdr_membership.term', $this->termLimitMin, '=', 'AND', 'fourthGroup');
            }else{
            	if(isset($this->termLimitMin) && !empty($this->termLimitMin))
                    $db->addWhere('sdr_membership.term', $this->termLimitMin, '>=', 'AND', 'fourthGroup');
                if(isset($this->termLimitMax) && !empty($this->termLimitMax))
                    $db->addWhere('sdr_membership.term', $this->termLimitMax, '<=', 'AND', 'fourthGroup');
            }
            
            $db->setGroupConj('fourthGroup', 'AND');
        }
        
        $db->addOrder('sdr_member.last_name', 'asc');
        $db->addOrder('sdr_member.first_name', 'asc');
        $db->addOrder('sdr_member.middle_name', 'asc');

        if($this->searchType == 'student') {
            $db->addJoin('RIGHT', 'sdr_member', 'sdr_student', 'id', 'id');
            $db->addJoin('RIGHT', 'sdr_member', 'sdr_student_registration', 'id', 'student_id');
            $db->addWhere('sdr_student_registration.term', Term::getCurrentTerm());
            // The above is most likely broke as shit.
        } else if($this->searchType == 'advisor') {
            $db->addJoin('RIGHT', 'sdr_member', 'sdr_advisor', 'id', 'id');
        }

        $db->addColumn('id');
        $db->addColumn('prefix');
        $db->addColumn('first_name');
        $db->addColumn('last_name');
        $db->addColumn('middle_name');
        $db->addColumn('suffix');
        $db->addColumn('username');

        $this->db = $db;
    }
}

?>
