<?php

abstract class StudentSearchView extends sdr\View{
    
    protected $studentSelectedCmd; // Command to exec when a student is selected
    protected $searchCmd;
    protected $form;
    
    public function __construct()
    {
        $this->form = new PHPWS_Form();
        $this->form->setMethod('get');
        $this->searchCmd = CommandFactory::getCommand('StudentSearch');
    }
    
    public function setStudentSelected(Command $studentCmd)
    {
        $this->studentSelectedCmd = $studentCmd;
    }
    
    public function show()
    {
        $this->form->addText('search_field');
        
        $this->form->addSubmit('submit', 'Search');
        
        $searchCmd = CommandFactory::getCommand('StudentSearch');
        
        // Tell the student search command what to do when someone chooses a result
        if(!is_null($this->studentSelectedCmd))
            $searchCmd->setAltCommand($this->studentSelectedCmd);
        
        $searchCmd->initForm($this->form);
    }
}
