<?php

PHPWS_Core::initModClass('sdr', 'TranscriptBrowserView.php');

class TranscriptPrintView extends TranscriptView {
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
        $tags['PRINT'] = ''; // Dummy tag to make the "print" icon appear

        $this->tpl = new PHPWS_Template('sdr');
        $this->tpl->setFile('TranscriptView.tpl');
        $this->tpl->setData($tags);

        // Render the Transcript Data
        $this->renderTranscript(FALSE);

        return $this->tpl->get();
    }

    protected function renderMembership(Membership $membership)
    {
        $hidden = $membership->getHidden();

        $data = array(
            'CLASS'        => 'transcript-visible',
            'ROLE'         => $membership->getRolesConcat(),
            'ORGANIZATION' => $membership->getOrganizationName(false));

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
}

?>
