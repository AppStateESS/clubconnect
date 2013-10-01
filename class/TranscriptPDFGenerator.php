<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'TranscriptView.php');
PHPWS_Core::initModClass('sdr', 'TranscriptPDF.php');

class TranscriptPDFGenerator extends TranscriptView
{
    protected $pdf;
    private $membershipsForTerm;
    private $y0;
    private $col;
    protected $settings;

    function __construct($transcript)
    {
        parent::__construct($transcript);
    
        $this->settings = new TranscriptPrintSettings(UserStatus::getUsername());
        $this->membershipsForTerm = array();
	$this->col = 0;
    }

    function show()
    {
        $this->pdf = new TranscriptPDF(
            $this->transcript->getStudent()->getFullName(),
            $this->transcript->getStudent()->getId(),
            $this->settings);
        $this->pdf->AliasNbPages();
        $this->pdf->AddPage();

        $this->renderTranscript(FALSE);

        // TODO: Should we do something more than just this?
        $filename = '/tmp/sdr_transcript.pdf';
        $this->pdf->Output($filename, 'F');
        return $filename;
    }

    protected function renderMembership(Membership $membership)
    {
        $roles = $membership->getRolesConcat();
        $org = $membership->getOrganizationName(false);
        $this->membershipsForTerm[] = "\t\t\t{$roles}, {$org}\n";
    }

    protected function renderTerm($term)
    {
        // Is this whole term going to cause a page break?
        // Funky code is required to ensure the whole term stays on the same page.
        // Should an entire term ever take up more than one page... ohes noes.
        // Also, there is probably some sort of corner case here.  Just be careful.
        $curpos = $this->pdf->GetY();
	$y0 = $curpos;
        $newpos = $curpos + (5 * (count($this->membershipsForTerm) + 1));
        if($newpos > $this->pdf->PageBreakTrigger) {
	    //this is where we need to do the column stuffs
	    //instead of adding a page here we need to go to the next column
	    // Method accepting or not automatic page break
	    if($this->col<1) {
                // Go to next column
                $this->SetCol($this->col+1);
                // Set y to top
                $this->pdf->SetY(56); //magic number!
                // Keep on page
	    }
	    else {
		// Go back to first column
		$this->SetCol(0);
		// Page break
		$this->pdf->AddPage();
	    }
	}

        $this->pdf->setFont('Arial', 'B', 14);
        $this->pdf->MultiCell(90, 5, Term::toString($term) . "\n");
        $this->pdf->setFont('Arial', NULL, 10);

        foreach($this->membershipsForTerm as $m) {
	    $this->pdf->MultiCell(90, 5, $m); //change 60 back to 0
        }
	$this->pdf->Ln();

        $this->membershipsForTerm = array();
    }
    /*protected function acceptPageBreak()
    {
	// Method accepting or not automatic page break
	if($this->col<1)
	{
	    // Go to next column
	    $this->SetCol($this->col+1);
	    // Set ordinate to top
	    $this->SetY($this->y0);
	    // Keep on page
	    return false;
	}
	else
	{
	    // Go back to first column
	    $this->SetCol(0);
	    // Page break
	    return true;
	}
	}*/
    protected function SetCol($col)
    {
	// Set position at a given column
	$this->col = $col;
	$x = 10+$col*95;
	$this->pdf->SetLeftMargin($x);
	$this->pdf->SetX($x);
    }
}

?>
