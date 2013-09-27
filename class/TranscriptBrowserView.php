<?php

PHPWS_Core::initModClass('sdr', 'TranscriptView.php');

class TranscriptBrowserView extends TranscriptView {

    protected $tpl;
    
    function __construct($transcript)
    {
        parent::__construct($transcript);
    }
    
    function show()
    {
        javascript('jquery');

        $tags['NAME'] = $this->transcript->getStudent()->getFullName();
        $tags['DATE'] = date('m/d/Y');

        $printViewCmd = CommandFactory::getCommand('ShowUserTranscriptPrintView');
        $printViewCmd->setMemberId($this->transcript->getStudent()->getId());
        $tags['PRINT_VIEW'] = $printViewCmd->getLink('Print View');
        
        if(UserStatus::isAdmin()) {
            $reqCmd = CommandFactory::getCommand('GenerateOfficialTranscript');
            $reqCmd->setMemberId($this->transcript->getStudent()->getId());
            $tags['REQUEST'] = $reqCmd->getLink('Generate Official');
        } else {
            $reqCmd = CommandFactory::getCommand('ShowUserTranscriptRequest');
            $tags['REQUEST'] = $reqCmd->getLink('Request Official');
        }

        $this->tpl = new PHPWS_Template('sdr');
        $this->tpl->setFile('TranscriptView.tpl');
        $this->tpl->setData($tags);

        // Render the Transcript Data
        $this->renderTranscript(TRUE);
        
        return $this->tpl->get();
    }

    protected function renderMembership(Membership $membership)
    {
        $hidden = $membership->getHidden();


        // CSS Class for Hidden or Shown
        $class = $hidden ? 'transcript-hidden' : 'transcript-visible';

        $data = array();
        $data['CLASS']        = $class;
        $data['ROLE']         = $membership->getRolesConcat();
        $data['ORGANIZATION'] = $membership->getOrganizationName(false);

	PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
	if(!GlobalLock::isLocked() || UserStatus::isAdmin()){
	  $this->getEditLink($membership, $data, $hidden);
	}
	
        $this->tpl->setCurrentBlock('membership_repeat');
        $this->tpl->setData($data);
        $this->tpl->parseCurrentBlock();
    }

    protected function renderTerm($term)
    {
        $data = array('TERM_LABEL' => Term::toString($term));

        $this->tpl->setCurrentBlock('term_repeat');
        $this->tpl->setData($data);
        $this->tpl->parseCurrentBlock();
    }
    
    protected function getEditLink($membership, & $data, $hidden){
      
        if($this->transcript->getStudent()->getUsername() == UserStatus::getUsername()) {
            // Get Appropriate Edit Link
            $cmd = CommandFactory::getCommand('UserTranscript' . ($hidden ? 'Show' : 'Hide') . 'Membership');
            $cmd->setMembershipId($membership->getId());
            $cmd->setMembershipType($membership->getType());
            $data['EDIT'] = PHPWS_Text::moduleLink($hidden ? '[show]' : '[hide]', 'sdr', $cmd->getRequestVars());
        }
    }
}

?>
