<?php

/**
 * Factory class for generating Membership objects.  This is a little
 * weird and probably breaks the design pattern a bit, but hear me out.
 * In the SDR, everything is more or less based around the Membership.
 * However, there are many ways of looking at a membership.  You can
 * look at it by organization, by user, by organization type, by approval,
 * etc, etc.  The membership object shall be kept mostly pure.  See its
 * documentation for more info on accessors and fields.  Use
 * MembershipFactory to get anything except a boring plain-jane old
 * Membership object.
 */

PHPWS_Core::initModClass('sdr', 'Membership.php');
PHPWS_Core::initModClass('sdr', 'Member.php');
PHPWS_Core::initModClass('sdr', 'Role.php');

class MembershipFactory
{
	/**
	 * Static function to get a set of memberships related to a given username.
	 * This means the given username may not necessarily be a member, but still
	 * has something to do with the organization; ie, an advisor.  Populates
	 * virtual fields as well.
	 *
	 * @param $username string The user to search on
	 * @param $term string The term to search in
	 * @return array Array of Membership
	 */
	public static function getMembershipsByUsername($username, $term)
	{
		$db = self::initDb();
		self::requestOrganization($db);
		self::requestRoles($db);
		self::whereUsername($db, $username);
		self::whereTerm($db, $term);
		$result = self::select($db);

		return self::plugMemberships($result);
	}

    public static function getConfirmedMembershipsByUsername($username, $term)
    {
        $db = self::initDb();
        self::requestOrganization($db);
        self::requestRoles($db);
        self::whereUsername($db, $username);
        self::whereTerm($db, $term);
        self::whereFullyApproved($db);
        $result = self::select($db);

        return self::plugMemberships($result);
    }

    public static function getPendingMembershipsByUsername($username, $term)
    {
        $db = self::initDb();
        self::requestOrganization($db);
        self::requestRoles($db);
        self::whereUsername($db, $username);
        self::whereTerm($db, $term);
        self::wherePending($db);
        $result = self::select($db);

        return self::plugMemberships($result);
    }
	
	public static function getMembershipsForTranscript($memberId)
	{
	    PHPWS_Core::initModClass('sdr', 'MemberFactory.php');
	
	    $db = self::initDb();
	    MemberFactory::request($db, 'sdr_membership', 'member_id');
        self::requestOrganization($db);
        self::requestRoles($db);
        self::whereMemberId($db, $memberId);
        self::whereFullyApproved($db);
        $result = self::select($db);

        return self::plugMemberships($result);
	}

	/**
	 * Static function to get a set of memberships related to a given organization
	 * ID.  All results will be entries from the "membership" table, including those
	 * with pending requests from either end.
	 *
	 * @param $organization int The organization ID to search on
	 * @param $term int The term to search in
	 * @return array Array of Membership
	 */
	public static function getMembershipsByOrganization($orgid, $term)
	{
		PHPWS_Core::initModClass('sdr', 'MemberFactory.php');
		 
		$db = self::initDb();
		self::requestRoles($db);
		self::whereOrganization($db, $orgid);
		self::whereTerm($db, $term);
		self::setOfficerAlphaOrder($db);
		MemberFactory::request($db, 'sdr_membership', 'member_id');
		$result = self::select($db);

		return self::plugMemberships($result);
	}

	public static function getAdminMembershipsByOrganization($orgid, $term)
	{
		PHPWS_Core::initModClass('sdr', 'MemberFactory.php');

		$db = self::initDb();
		MemberFactory::request($db, 'sdr_membership', 'member_id');
		self::requestRoles($db);
		self::whereOrganization($db, $orgid);
		self::whereTerm($db, $term);
		self::whereAdmin($db);
		self::setOfficerAlphaOrder($db);
		$result = self::select($db);

		return self::plugMemberships($result);
	}

    public static function getMembershipByOrganizationMember($orgid, $memberid, $term)
    {
        $db = self::initDb();
        self::requestRoles($db);
        self::whereOrganization($db, $orgid);
        self::whereMembershipMemberId($db, $memberid);
        self::whereTerm($db, $term);
        $result = self::select($db);

        return self::plugMemberships($result);
    }

	/**
	 * Static function to get an individual membership by the membership id.
	 * @param $id
	 * @return unknown_type
	 */
	public static function getMembershipById($id)
	{
		PHPWS_Core::initModClass('sdr', 'MemberFactory.php');
		 
		$db = self::initDb();
		MemberFactory::request($db, 'sdr_membership', 'member_id');
		self::requestOrganization($db);
		self::whereMembershipId($db, $id);

		$result = self::select($db);

		if(sizeof($result) <= 0){
			return;
		}

		$memberships = self::plugMemberships($result);

		return $memberships[$id];
	}

	/**
	 * Static function to get an individual membership by the membership id. Includes
	 * all roles and the student's name.
	 * @param $id
	 * @return unknown_type
	 */
	public static function getMembershipByIdWithRoles($id)
	{
		PHPWS_Core::initModClass('sdr', 'MemberFactory.php');

		$db = self::initDb();
		MemberFactory::request($db, 'sdr_membership', 'member_id');
		self::requestRoles($db);
		self::requestOrganization($db);
		self::whereMembershipId($db, $id);
		self::setRoleTitleAlphaOrder($db);
		 
		$result = self::select($db);

		if(sizeof($result) <= 0){
			return;
		}

		$memberships = self::plugMemberships($result);
		 
		return $memberships[$id];
	}

	/**
	 * Static function to get an individual membership for the given user to
	 * the given organization within the given term (or current term if null);
	 *
	 * @param $org_id
	 * @param $user
	 * @param $term
	 * @return Membership $membership
	 */
	public static function getUserMembershipByOrganization($org_id, $user, $term=null){
		if(is_null($term))
		$term = Term::getSelectedTerm();

		$db = self::initDb();
		self::whereOrganization($db, $org_id);
		self::whereUsername($db, $user);
		self::whereTerm($db, $term);

		$result = self::select($db);

		if(sizeof($result) < 1 || sizeof($result) > 1){
			return;
		}

		$membership = new Membership($result[0]);

		return $membership;
	}

	protected static function plugMemberships(array $result)
	{
		$memberships = array();

		foreach($result as $r) {
			if(!isset($memberships[$r['id']])) {
				$memberships[$r['id']] = new Membership($r);
				$memberships[$r['id']]->setOfficer(false);

				if(isset($r['_member_id'])) {
					$m = MemberFactory::plugMember($r);
					$memberships[$r['id']]->setMember($m);
				}
			}

			$m = $memberships[$r['id']];

			if(isset($r['_role_id']) && !is_null($r['_role_id'])){
				$role = new Role();
				$role->setId($r['_role_id']);
				$role->setTitle($r['_role_title']);
				$role->setRank($r['_role_rank']);
				$m->addRole($role);

                if($role->isOfficer())
                    $m->setOfficer(TRUE);
               
                if($role->isAdvisor())
                    $m->setAdvisor(TRUE);
			}
		}

		return $memberships;
	}

	protected static function initDb()
	{
		$db = new PHPWS_DB('sdr_membership');
		$db->addColumn('sdr_membership.*');
		return $db;
	}

	protected static function select(&$db)
	{
		$result = $db->select();

		if(PHPWS_Error::logIfError($result)) {
			PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
			throw new DatabaseException('Could not select memberships by username.');
		}
		 
		return $result;
	}

	protected static function joinOrganizations(&$db)
	{
		$db->addJoin('left', 'sdr_membership', 'sdr_organization_recent', 'organization_id', 'id');
	}

	protected static function joinRoles(&$db)
	{
		$db->addJoin('left', 'sdr_membership', 'sdr_membership_role', 'id', 'membership_id');
		$db->addJoin('left', 'sdr_membership_role', 'sdr_role', 'role_id', 'id');
	}

	protected static function requestOrganization(&$db)
	{
		self::joinOrganizations($db);
		$db->addColumn('sdr_organization_recent.name', null, '_organization_name');
	}

	protected static function requestRoles(&$db)
	{
		self::joinRoles($db);
		$db->addColumn('sdr_role.id', null, '_role_id');
		$db->addColumn('sdr_role.title', null, '_role_title');
		$db->addColumn('sdr_role.rank', null, '_role_rank', false, false, 0);
	}

	protected static function whereMembershipId(&$db, $id)
	{
		$db->addWhere('sdr_membership.id', $id);
	}

    protected static function whereMembershipMemberId(&$db, $id)
    {
        $db->addWhere('sdr_membership.member_id', $id);
    }

	protected static function whereMemberId(&$db, $id)
	{
	    $db->addWhere('sdr_member.id', $id);
	}

    protected static function wherePending(&$db)
    {
        $db->addWhere('sdr_membership.organization_approved', 0, NULL, 'OR', 'pending');
        $db->addWhere('sdr_membership.student_approved', 0, NULL, 'OR', 'pending');
    }

    protected static function whereFullyApproved(&$db)
    {
        self::whereOrganizationApproved($db);
        self::whereStudentApproved($db);
    }

    protected static function whereOrganizationApproved(&$db)
    {
        $db->addWhere('sdr_membership.organization_approved', 1);
    }

    protected static function whereStudentApproved(&$db)
    {
        $db->addWhere('sdr_membership.student_approved', 1);
    }
	
	protected static function whereUsername(&$db, $username)
	{
		PHPWS_Core::initModClass('sdr', 'MemberFactory.php');
		MemberFactory::join($db, 'sdr_membership', 'member_id');
		$db->addWhere('sdr_member.username', $username);
	}

	protected static function whereOrganization(&$db, $orgid)
	{
		$db->addWhere('sdr_membership.organization_id', $orgid);
	}

	protected static function whereTerm(&$db, $term)
	{
		$db->addWhere('sdr_membership.term', $term);
	}

	protected static function whereAdmin(&$db)
	{
		$db->addWhere('sdr_membership.administrator', 1);
	}

	protected static function setOfficerAlphaOrder(&$db)
	{
		$db->addOrder('sdr_membership.organization_approved asc');
		$db->addOrder('sdr_membership.student_approved desc');
		$db->addOrder('coalesce(sdr_role.rank, 0) desc');
		$db->addOrder('sdr_member.last_name asc');
		$db->addOrder('sdr_member.first_name asc');
		$db->addOrder('sdr_member.middle_name asc');
	}

	protected static function setRoleTitleAlphaOrder(&$db)
	{
		$db->addOrder('sdr_role.title asc');
	}
}
