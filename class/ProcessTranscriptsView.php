<?php

class ProcessTranscriptsView extends sdr\View {
    public $pager;
    
    public function __construct(Command $viewCommand, $showProcessed = FALSE)
    {
        PHPWS_Core::initCoreClass('DBPager.php');
        PHPWS_Core::initModClass('sdr', 'TranscriptRequest.php');

        $this->pager = new DBPager('sdr_transcript_request', 'TranscriptRequest');
        $this->pager->setModule('sdr');
        $this->pager->setLink('index.php?module=sdr');
        $this->pager->setEmptyMessage(dgettext('sdr', 'No transcript requests are currently pending.'));
        $this->pager->setTemplate('ProcessTranscriptsView.tpl');
        $this->pager->addRowTags('processTranscriptsPagerTags', $viewCommand, $showProcessed);

        if(!$showProcessed)
            $this->pager->db->addWhere('processed', 0);

        $tags = array();
        $tags['TITLE'] = dgettext('sdr', 'Co-Curricular Transcript Requests');
        $tags['MENU'] = ''; // TODO: Menu for selecting between All Requests and Pending Requests
        $tags['NAME_LABEL'] = dgettext('sdr', 'Name');
        $tags['DATE_LABEL'] = dgettext('sdr', 'Date Requested');
        if($showProcessed)
            $tags['STATUS_LABEL'] = dgettext('sdr', 'Status');
        $tags['ACTIONS_LABEL'] = dgettext('sdr', 'Actions');
        $this->pager->addPageTags($tags);
    }
    
	function show()
	{
        return $this->pager->get();
	}
}
