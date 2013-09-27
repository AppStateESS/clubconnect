<?php

/**
 * MembershipSet - A Composite Adapater pattern class
 * used for wrangling all the various memberships types into one place/class
 * @author jbooker
 *
 */
PHPWS_Core::initModClass('sdr', 'Membership.php');
PHPWS_Core::initModClass('sdr', 'Student_Employment.php');
PHPWS_Core::initModClass('sdr', 'SDR_Academics.php');

class MembershipSet {

    private $member_id;
    private $memberships; // The set of memberships for this student

    function __construct($member_id){
        $this->memberships = array();

        $this->member_id = $member_id;

        // Get the various memberships
        // Each function adds its results to the 'memberships' member variable
        $this->getClubMemberships();
        $this->getDeansChancellors();
        $this->getScholarships();
        $this->getStudentEmployment();
    }

    /**
     * Returns an array of all the membership objects from this MembershipSet
     * @return array - Membership[]
     */
    public function getMemberships()
    {
        return $this->memberships;
    }

    /**
     * Adds a Membership object to this MembershipSet.
     * @param $membership
     * @return void
     */
    private function addMembership(Membership $membership)
    {
        $this->memberships[] = $membership;
    }

    /**
     * Creates the Membership objects for the standard club memberships (for the given member_id), and adds them to this MembershipSet
     * @return void
     */
    private function getClubMemberships()
    {
        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        
        $result = MembershipFactory::getMembershipsForTranscript($this->member_id);

        // Add each membership object, if any, to the set
        if(!empty($result)) {
            foreach($result as $membership) {
                $membership->setType(MBR_TYPE_CLUB);
                $this->addMembership($membership);
            }
        }

    }

    private function getDeansChancellors()
    {
        $result = SDR_Deans_Chancellors::getTranscriptItems($this->member_id);
        if(is_null($result)) return;
        
        foreach($result as $membership){
            $membership->setType(MBR_TYPE_DC_LIST);
            $this->addMembership($membership);
        }
    }

    private function getScholarships()
    {
        $result = SDR_Scholarships::getTranscriptItems($this->member_id);
        if(is_null($result)) return;
        
        foreach($result as $membership){
            $membership->setType(MBR_TYPE_SCHOLARSHIP);
            $this->addMembership($membership);
        }
    }

    private function getStudentEmployment()
    {
        $result = Student_Employment::getTranscriptEmployment($this->member_id);
        if(is_null($result)) return;
        
        foreach($result as $membership){
            $membership->setType(MBR_TYPE_EMPLOYMENT);
            $this->addMembership($membership);
        }
    }
}
?>
