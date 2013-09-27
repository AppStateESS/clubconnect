<?php

/**
 * ShowMemberInfoCommand - Command to handle showing student info
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowMemberInfoCommand extends Command {
    
    protected $member_id;

    public function getParams()
    {
        return array('member_id');
    }
    
    public function setMemberId($id){
        $this->member_id = $id;
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'MemberProfileView.php');
        $member = new Member($this->member_id);
        $view = new MemberProfileView($member);
        $context->setContent($view->show());
    }
}

?>
