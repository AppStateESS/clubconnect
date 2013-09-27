<?php

PHPWS_Core::initModClass('sdr', 'MemberProfileEdit.php');

class AdvisorProfileEdit extends MemberProfileEdit
{
	public function __construct(Advisor $a)
	{
		$this->member = $a;
	}
	
	protected function showPart(PHPWS_Form &$form)
	{
		$m = $this->member;
		
		$form->addText('home_phone', $m->getHomePhone());
		$form->setLabel('home_phone', dgettext('sdr', 'Home Phone'));
		
		$form->addText('office_phone', $m->getOfficePhone());
		$form->setLabel('office_phone', dgettext('sdr', 'Office Phone'));
		
		$form->addText('cell_phone', $m->getCellPhone());
		$form->setLabel('cell_phone', dgettext('sdr', 'Cell Phone'));
		
		$form->addText('office_location', $m->getOfficeLocation());
		$form->setLabel('office_location', dgettext('sdr', 'Office Location'));
		
		$form->addText('department', $m->getDepartment());
		$form->setLabel('department', dgettext('sdr', 'Department'));
	}
}