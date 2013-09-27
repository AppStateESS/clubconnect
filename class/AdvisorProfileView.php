<?php

PHPWS_Core::initModClass('sdr', 'MemberProfileView.php');

class AdvisorProfileView extends MemberProfileView
{	
	public function __construct(Advisor $a)
	{
		$this->member = $a;
	}
	
	protected function showPart()
	{
		$tpl = array();
		$a = $this->member;
		
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
}