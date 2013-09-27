<?php

PHPWS_Core::initModClass('sdr', 'StudentProfileView.php');

class StudentProfileEdit extends StudentProfileView
{
	public function __construct(Student $s)
	{
		$this->member = $s;
	}
	
	protected function showPart()
	{
		PHPWS_Core::initModClass('sdr', 'exception/UnsupportedFunctionException.php');
		throw new UnsupportedFunctionException('Edit Student Profile has not yet been implemented.  Try an Advisor instead.');
	}
}