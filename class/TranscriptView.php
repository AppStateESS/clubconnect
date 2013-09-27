<?php

abstract class TranscriptView extends sdr\View{
	
	protected $transcript      = null;
	protected $memberships     = null;
	
	function __construct($transcript)
	{
		$this->transcript = $transcript;

        $this->memberships = $this->transcript->getMembershipsByTerm();

        ksort($this->memberships);
        $this->memberships = array_reverse($this->memberships, true);
	}
	
	function show()
	{
	    javascript('/jquery');
	    
	    $tags['NAME'] = $this->transcript->getStudent()->getFullName();
	    $tags['DATE'] = date('m/d/Y');

	    $tpl = new PHPWS_Template('sdr');
        $tpl->setFile('transcript/transcript.tpl');
	    
	    $tpl->setData($tags);
	    
	    $this->tpl = $tpl;
	}

    protected abstract function renderMembership(Membership $membership);
    protected abstract function renderTerm($term);

    protected final function renderTranscript($includeHidden = FALSE)
    {
        // For each term with its set of memberships
        foreach($this->memberships as $term=>$term_memberships){
            
            $show_term = false; // Whether or not to show the heading for this term, in case *all* of the memberships for this term are hidden
            
            // For each membership in this term
            foreach($term_memberships as $membership){
                
                if(!$includeHidden && $membership->getHidden() == true){
                    continue;
                }else{
                    // We're at least showing this membership, so we need to show the term
                    $show_term = true;
                }

                $this->renderMembership($membership);
            }
            
            if($show_term) {
                $this->renderTerm($term);
            }
        }
    }
}

?>
