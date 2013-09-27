<?php

/**
 * Saves an edited Member
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class SaveMemberCommand extends Command
{
	private $memberId;
	
	function setMemberId($id)
	{
		$this->memberId = $id;
	}
	
	function getRequestVars()
	{
		$vars = array('action' => 'SaveMember');
		
		if(isset($this->memberId)) {
			$vars['member_id'] = $this->memberId;
		}
		
		return $vars;
	}
	
	function execute(CommandContext $context)
	{
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You must be an admin to change a Member record.');
        }

		if(!isset($this->memberId)) {
			$this->memberId = $context->get('member_id');
		}
		
		$memberId = $this->memberId;

        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'Advisor.php');

        $m = new Member($memberId);
        $context->plugObject($m);
        if($m->isAdvisor()) {
            $context->plugObject($m->getAdvisor());
        } else {
            $a = new Advisor();
            $context->plugObject($a);
            $m->setAdvisor($a);
        }

        $m->save();
		
		$cmd = CommandFactory::getCommand('ShowMemberInfo');
		$cmd->setMemberId($m->getId());
		$cmd->redirect();
	}
}
