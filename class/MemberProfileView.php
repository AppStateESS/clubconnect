<?php

PHPWS_Core::initModClass('sdr', 'Member.php');

class MemberProfileView extends sdr\View
{
    protected $member;

    public function __construct(Member $m)
    {
        $this->member = $m;
    }
	
	public function show()
	{
		if(!UserStatus::isAdmin()) {
			PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
			throw new PermissionException('You do not have permission to view a member profile.');
		}

        $tpl = array();
		$m = $this->member;

        $tpl['TITLE']             = $m->getFullName();

		PHPWS_Core::initModClass('sdr', 'MemberAdminMenu.php');
		$menu = new MemberAdminMenu($m);
        $tpl['MENU'] = $menu->show();

        $tpl['COMMON'] = $this->createPanel(
            dgettext('sdr', 'Common Demographics'),
            array(
                'Banner ID'         => $m->id,
//                'Email'             => $m->email,
                'Username'          => $m->username,
                'First Name'        => $m->first_name,
                'Middle Name'       => $m->middle_name,
                'Last Name'         => $m->last_name));

        if($m->isStudent()) $s = $m->_student;
        $tpl['STUDENT'] = $this->createPanel(
            dgettext('sdr', 'Student Demographics'),
            !$m->isStudent() ? dgettext('sdr', 'No Student Record Available') :
            array(
                'Gender'        => $s->gender,
                'Ethnicity'     => $s->ethnicity,
                'Birthday'      => $s->birthdate,
                'Citizen'       => $s->citizen,
                'Date Enrolled' => $s->date_enrolled));
//                'Preferred Name'  => $s->preferred,
//                'Major'           => $s->major,
//                'Class'           => $s->class,
//                'Level'           => $s->level,
//                'Confidentiality' => $s->confidentiality));

        if($m->isAdvisor()) $a = $m->_advisor;
        $tpl['ADVISOR'] = $this->createPanel(
            dgettext('sdr', 'Advisor Demographics'),
            !$m->isAdvisor() ? dgettext('sdr', 'No Advisor Record Avaialble') :
            array (
                'Department'   => $a->department,
                'Home Phone'   => $a->home_phone,
                'Office Phone' => $a->office_phone,
                'Cell Phone'   => $a->cell_phone,
                'Office'       => $a->office_location));
//                'Title'      => $a->title,
//                'Phone'      => $a->phone));

		return PHPWS_Template::process($tpl, 'sdr', 'MemberProfileView.tpl');
	}

    protected function createPanel($title, $content)
    {
        $tpl = array();
        $tpl['TITLE'] = $title;

        if(is_array($content)) {
            $tpl['DATA'] = array();
            foreach($content as $key => $val) {
                $tpl['DATA'][] = array(
                    'KEY' => $key,
                    'VAL' => $val);
            }
        } else {
            $tpl['EMPTY'] = $content;
        }

        return PHPWS_Template::process($tpl, 'sdr', 'PanelKeyVal.tpl');
    }

    protected function showStudentPart()
    {
		$tpl = array();
		$s = $this->member->_student;
		
		$tpl['GENDER'] = self::translateByTable($s->getGender(), Student::GENDER_VALUES());
		$tpl['ETHNICITY'] = self::translateByTable($s->getEthnicity(), Student::ETHNICITY_VALUES());
		$tpl['CITIZENSHIP'] = self::translateByTable($s->getCitizen(), Student::CITIZEN_VALUES());
		$tpl['BIRTHDATE'] = self::translateDate($s->getBirthdate());
		$tpl['DATE_ENROLLED'] = self::translateDate($s->getDateEnrolled());
		
		$tpl['GENDER_LABEL'] = dgettext('sdr', 'Gender');
		$tpl['ETHNICITY_LABEL'] = dgettext('sdr', 'Ethnicity');
		$tpl['CITIZEN_LABEL'] = dgettext('sdr', 'Citizen');
		$tpl['BIRTHDATE_LABEL'] = dgettext('sdr', 'Birthday');
		$tpl['DATE_ENROLLED_LABEL'] = dgettext('sdr', 'Date Enrolled');

		return PHPWS_Template::process($tpl, 'sdr', 'StudentProfileView.tpl');
    }

    protected function showAdvisorPart()
    {
		$tpl = array();
		$a = $this->member->_advisor;
		
		$tpl['HOME_PHONE'] = $a->getHomePhone();
		$tpl['OFFICE_PHONE'] = $a->getOfficePhone();
		$tpl['CELL_PHONE'] = $a->getCellPhone();
		$tpl['OFFICE_LOCATION'] = $a->getOfficeLocation();
		$tpl['DEPARTMENT'] = $a->getDepartment();
		
		$tpl['HOME_PHONE_LABEL'] = dgettext('sdr', 'Home Phone');
		$tpl['OFFICE_PHONE_LABEL'] = dgettext('sdr', 'Office Phone');
        $tpl['CELL_PHONE_LABEL'] = dgettext('sdr', 'Cell Phone');
        $tpl['OFFICE_LOCATION_LABEL'] = dgettext('sdr', 'Office Location');
        $tpl['DEPARTMENT_LABEL'] = dgettext('sdr', 'Department');
		
		return PHPWS_Template::process($tpl, 'sdr', 'AdvisorProfileView.tpl');
    }

    public static function translateByTable($banner, $table)
    {    	
    	if(!isset($table[$banner])) {
            SDR::silentNotify(new InvalidArgumentException("$banner is not an acceptable value."));
            return $banner;
    	}
    	
    	return $table[$banner];
    }
    
    public static function translateDate($date)
    {
    	return $date;
    }
}
