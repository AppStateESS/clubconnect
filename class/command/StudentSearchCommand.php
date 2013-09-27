<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class StudentSearchCommand extends Command {
    
    private $altCommand; // Stores a command to be encapsulated in this command
    
    function getRequestVars()
    {
        $vars = array('action' => 'StudentSearch');
        
        if(isset($this->altCommand) && !is_null($this->altCommand)){
            $altVars = $this->altCommand->getRequestVars();
            
            // Get the alternate action
            $altAction = $altVars['action'];
            
            // Unset it so it doesn't conflict
            unset($altVars['action']);
            
            // Reset it under a different name
            $altVars['altAction'] = $altAction;
            
            return array_merge($vars, $altVars);
        }
        
        return $vars;
    }
    
    function setAltCommand(Command $cmd)
    {
        $this->altCommand = $cmd;
    }
    
    function getAltCommand(CommandContext $context)
    {
        return CommandFactory::getCommand($context->get('altAction'));
    }
    
    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'StudentSearch.php');
        
        $search = new StudentSearch();
        
        // Setup the search params
        $search->setStudentSelectedCommand($this->getAltCommand($context));
        $search->setOrganizationId($context->get('organization_id')); // The target organization of the altCommand, if necessary
        $search->setSearchField($context->get('search_field'));
        $search->limitToOrganization($context->get('organization'));
        $search->setTermLimitMin($context->get('minTerm'));
        $search->setTermLimitMax($context->get('maxTerm'));
        
        $search->doSearch();
        
        // Show the results
        $context->setContent($search->getResultsView()->show());
    }
}

?>
