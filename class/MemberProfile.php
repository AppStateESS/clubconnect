<?php

/**
 * Member Profile Controller
 * 
 * Mainly for displaying and editing.  Also used for "creating" new users.
 * 
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'MemberFactory.php');
PHPWS_Core::initModClass('sdr', 'Member.php');
PHPWS_Core::initModClass('sdr', 'Student.php');
PHPWS_Core::initModClass('sdr', 'Address.php');
PHPWS_Core::initModClass('sdr', 'Advisor.php');

class MemberProfile
{
	private $member;
	
	public function __construct($member)
	{
		if(is_a($member, 'Member')) {
			$this->member = $member;
		} else if($member > 0) {
            $this->member = MemberFactory::getMemberById($member);
        }
	}
	
	public function view()
	{
        $html = '';
        if($this->member->isStudent()) {
			PHPWS_Core::initModClass('sdr', 'StudentProfileView.php');
            $view = new StudentProfileView($this->member);
            $html .= $view->show();
        }
        if($this->member->isAdvisor()) {
			PHPWS_Core::initModClass('sdr', 'AdvisorProfileView.php');
			$view = new AdvisorProfileView($this->member);
            $html .= $view->show();
		}
        return $html;
	}
	
	public function edit()
	{
		if(is_a($this->member, 'Student')) {
			PHPWS_Core::initModClass('sdr', 'StudentProfileEdit.php');
			$view = new StudentProfileEdit($this->member);
			return $view->show();
		} else if(is_a($this->member, 'Advisor')) {
			PHPWS_Core::initModClass('sdr', 'AdvisorProfileEdit.php');
			$view = new AdvisorProfileEdit($this->member);
			return $view->show();
		}
	}
	
	public function save(CommandContext $context)
	{
		$m = $this->member;
		
		$context->plugObject($m);
		
		return $m->save();
	}
}
