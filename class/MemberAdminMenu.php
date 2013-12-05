<?php

  /**
   * SDR Member Administration Menu Controller
   * Displays a permission-sensitive menu for actions to be performed
   * on an individual member.
   * 
   * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
   */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');

class MemberAdminMenu extends CommandMenu
{
    protected $member;

    public function __construct($member)
    {
        $this->member = $member;
        parent::__construct();
    }

    protected function setupCommands()
	{
        $member = $this->member;
        if(UserStatus::isAdmin()) {
            $user = $member->getUsername();
            $cmd = CommandFactory::getCommand('WearMask');
            $cmd->setUsername($user);
            $this->addCommand("Login As $user", $cmd);

            $commands = array();
            $commands['View Profile'] = 'ShowMemberInfo';
            $commands['Transcript']   = 'ShowUserTranscript';
			
			foreach($commands as $text=>$command) {
				$cmd = CommandFactory::getCommand($command);
				$cmd->setMemberId($member->getId());
				$this->addCommand($text, $cmd);
			}
		}
	}
}

?>
