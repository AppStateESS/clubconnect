<?php

abstract class MemberProfileEdit extends sdr\View
{
	protected $member;
	
	protected abstract function showPart(PHPWS_Form &$form);
	
	public function show()
	{
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit a member profile.');
        }
        
        $m = $this->member;
        
		$form = new PHPWS_Form('Edit Member Profile');
		$cmd = CommandFactory::getCommand('SaveMemberProfile');
		$cmd->setMemberId($m->getId());
		$cmd->initForm($form);
		
		$form->addText('username', $m->getUsername());
		$form->setLabel('username', dgettext('sdr', 'Current ASU Username'));
		$form->setSize('username', 10);
		
		$form->addText('prefix', $m->getPrefix());
		$form->setLabel('prefix', dgettext('sdr', 'Prefix'));
		$form->setSize('prefix', 5);
		
		$form->addText('first_name', $m->getFirstName());
		$form->setLabel('first_name', dgettext('sdr', 'First Name'));
		$form->setSize('first_name', 15);
		
		$form->addText('middle_name', $m->getMiddleName());
		$form->setLabel('middle_name', dgettext('sdr', 'Middle Name'));
        $form->setSize('middle_name', 15);
		
		$form->addText('last_name', $m->getLastName());
		$form->setLabel('last_name', dgettext('sdr', 'Last Name'));
        $form->setSize('last_name', 15);
        
        $form->addText('suffix', $m->getSuffix());
        $form->setLabel('suffix', dgettext('sdr', 'Suffix'));
        $form->setSize('suffix', 5);
		
		$this->showPart($form);
		
		$form->addSubmit('Save Profile');
		
		$tpl = array();
		$tpl['CHILD_PART'] = PHPWS_Template::process($form->getTemplate(), 'sdr', 'MemberProfileEdit.tpl');
		if($m->getId() > 0) {
		    $tpl['NAME'] = 'Editing Member Profile';
		    $tpl['ID'] = $m->getId();
		} else {
			$tpl['NAME'] = 'Creating New Member';
		}
		
		PHPWS_Core::initModClass('sdr', 'MemberAdminMenu.php');
        $menu = new MemberAdminMenu($m);
        $tpl['MENU'] = $menu->show();
		
		return PHPWS_Template::process($tpl, 'sdr', 'MemberProfileView.tpl');
	}
}
