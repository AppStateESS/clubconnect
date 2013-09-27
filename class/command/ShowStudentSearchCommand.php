<?php

/**
 * Command class which handles creating the view to show the student search interface
 */

PHPWS_Core::initModClass('sdr', 'LockableCommand.php');

class ShowStudentSearchCommand extends LockableCommand
{
    public $organizationId;
    
    public function setOrganizationId($id){
        $this->organizationId = $id;
    }
    
    function getRequestVars()
    {
        return array('action' => 'ShowStudentSearch', 'organization_id'=>$this->organizationId);
    }
    
    function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'StudentSearchClubView.php');
        
        $search = new StudentSearchClubView();
        
        $studentCmd = CommandFactory::getCommand('AddMember');
        $studentCmd->setOrganizationId($context->get('organization_id'));
        
        $search->setStudentSelected($studentCmd);
        
        $context->setContent($search->show());
    }
}
?>
