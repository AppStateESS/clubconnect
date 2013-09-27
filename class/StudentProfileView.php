<?php

PHPWS_Core::initModClass('sdr', 'MemberProfileView.php');

class StudentProfileView extends MemberProfileView
{
	public function __construct(Member $s)
	{
		$this->member = $s;
	}
	
	protected function showPart()
	{
		$tpl = array();
		$s = $this->member;
		
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
		/*
		PHPWS_Core::initModClass('AddressView.php');
		$view = new AddressView();
		$addresses = array();
		foreach($s->getAddresses() as $address) {
			$view->setAddress($address);
			$addresses[]['ADDRESS'] = $view->show();
		}
		$tpl['ADDRESSES'] = $addresses;
		
		PHPWS_Core::initModClass('EnrollmentView.php');
		$view = new EnrollmentView();
		$enrollments = array();
		foreach($s->getEnrollments() as $enrollment) {
			$view->setEnrollment($enrollment);
			$enrollments[]['ENROLLMENT'] = $view->show();
		}
		$tpl['ENROLLMENTS'] = $enrollments;
		*/
		
		// TODO: "Make Advisor" button
		
		return PHPWS_Template::process($tpl, 'sdr', 'StudentProfileView.tpl');
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
