<?php

PHPWS_Core::initModClass('sdr', 'Member.php');
PHPWS_Core::initModClass('sdr', 'Student.php');
PHPWS_Core::initModClass('sdr', 'Advisor.php');

class MemberFactory
{
    public static function fromLogin()
    {
        return new Member(NULL, UserStatus::getUsername());
    }

	public static function request(PHPWS_DB &$db, $table, $col, $type = 'left')
	{
        self::join($db, $table, $col, $type);
    }

    public static function requestMember(PHPWS_DB &$db)
    {
		$db->addColumn('sdr_member.id', null, '_member_id');
        $db->addColumn('sdr_member.username', null, '_member_username');
        $db->addColumn('sdr_member.prefix', null, '_member_prefix');
        $db->addColumn('sdr_member.first_name', null, '_member_firstname');
        $db->addColumn('sdr_member.middle_name', null, '_member_middlename');
        $db->addColumn('sdr_member.last_name', null, '_member_lastname');
        $db->addColumn('sdr_member.suffix', null, '_member_suffix');
	}
	
	public static function join(PHPWS_DB &$db, $table, $col, $type = 'left')
	{
		$db->addJoin($type, $table, 'sdr_member', $col, 'id');
        self::requestMember($db);
	}
	
	public static function plugMember(array $r)
	{
		$m = new Member();
		$m->setId($r['_member_id']);
		$m->setUsername($r['_member_username']);
		$m->setPrefix($r['_member_prefix']);
		$m->setFirstName($r['_member_firstname']);
		$m->setMiddleName($r['_member_middlename']);
		$m->setLastName($r['_member_lastname']);
		$m->setSuffix($r['_member_suffix']);
		
        if(isset($r['_student_id'])) {
            $s = new Student();
            $s->setId($r['_student_id']);
            $s->setGender($r['_student_gender']);
            $s->setEthnicity($r['_student_ethnicity']);
            $s->setBirthdate($r['_student_birthdate']);
            $s->setCitizen($r['_student_citizen']);
            $s->setDateEnrolled($r['_student_date_enrolled']);
            $m->setStudent($s);
        }

        if(isset($r['_advisor_id'])) {
            $a = new Advisor();
            $a->setId($r['_advisor_id']);
            $a->setHomePhone($r['_advisor_home_phone']);
            $a->setOfficePhone($r['_advisor_office_phone']);
            $a->setCellPhone($r['_advisor_cell_phone']);
            $a->setOfficeLocation($r['_advisor_office_location']);
            $a->setDepartment($r['_advisor_department']);
            $m->setAdvisor($a);
        }

		return $m;
	}
	
	public static function getMemberById($id)
	{
		$db = self::initDb();
		self::requestStudent($db);
		self::requestAdvisor($db);
		self::requestEnrollments($db);
		self::requestMemberships($db);
		self::whereMemberId($db, $id);
		
		$result = self::select($db);
		
		if(sizeof($result) <= 0) {
			return;
		}
		
		$members = self::plugMembers($result, TRUE);
		
		return $members[$id];
	}
	
	protected static function plugMembers(array $result, $addresses = FALSE, $enrollments = FALSE, $memberships = FALSE)
    {
		$members = array();

        foreach($result as $r) {
            $id = $r['_member_id'];
			if(!isset($members[$id])) {
                $members[$id] = self::plugMember($r);
			}
			
			if($memberships) $members[$id]->loadMemberships();
		}

		return $members;
	}
    
    protected static function joinStudent(PHPWS_DB &$db, $type='left outer')
    {
        $db->addJoin($type, 'sdr_member', 'sdr_student', 'id', 'id');
    }
	
	protected static function requestStudent(PHPWS_DB &$db)
	{
		self::joinStudent($db);
        $db->addColumn('sdr_student.id', null, '_student_id');
		$db->addColumn('sdr_student.gender', null, '_student_gender');
		$db->addColumn('sdr_student.ethnicity', null, '_student_ethnicity');
		$db->addColumn('sdr_student.birthdate', null, '_student_birthdate');
		$db->addColumn('sdr_student.citizen', null, '_student_citizen');
		$db->addColumn('sdr_student.date_enrolled', null, '_student_date_enrolled');
	}
	
	protected static function joinAdvisor(PHPWS_DB &$db, $type='left outer')
	{
		$db->addJoin($type, 'sdr_member', 'sdr_advisor', 'id', 'id');
	}
	
	protected static function requestAdvisor(PHPWS_DB &$db)
	{
		self::joinAdvisor($db);
        $db->addColumn('sdr_advisor.id', null, '_advisor_id');
		$db->addColumn('sdr_advisor.home_phone', null, '_advisor_home_phone');
		$db->addColumn('sdr_advisor.office_phone', null, '_advisor_office_phone');
		$db->addColumn('sdr_advisor.cell_phone', null, '_advisor_cell_phone');
		$db->addColumn('sdr_advisor.office_location', null, '_advisor_office_location');
		$db->addColumn('sdr_advisor.department', null, '_advisor_department');
	}
	
	protected static function requestEnrollments(&$db)
	{
		//self::joinEnrollments($db);
	}
	
	protected static function requestMemberships(&$db)
	{
		// TODO: wtf?
	}
	
	protected static function whereMemberId(&$db, $id)
	{
		$db->addWhere('sdr_member.id', $id);
	}
	
	protected static function initDb()
	{
		$db = new PHPWS_DB('sdr_member');
        self::requestMember($db);
		return $db;
	}
	
	protected static function select(PHPWS_DB &$db)
	{
		$result = $db->select();
		
		if(PHPWS_Error::logIfError($result)) {
			PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
			throw new DatabaseException('Could not select members.');
		}
		
		return $result;
	}
}
