<?php

PHPWS_Core::requireInc('sdr', 'FPDF.php');

class TranscriptPDF extends FPDF
{
    var $name;
    var $sid;
    function Header() {
        //Select Arial bold 15
        $this->SetFont('Arial','B',14);
        //get to the correct starting point....
        $this->SetY(35);
        //Move to the right
        $this->Cell(8);
        //Title
        $this->Cell(30,10,$this->name);
        //Move more to the right
        $this->Cell(136);
        //Some random text
        $this->Cell(10,10,$this->sid);
        //Line break
        $this->Ln(20);
    }

    function Footer() {
        //align for paper
        $this->SetY(-15);
        //Select Arial Bold 14
        $this->SetFont('Arial','B',14);
	//here we need to check if the last page was one or two columns
	//if x is 10, left col
	//if x is 105, right col
	if($this->getX() == 10) //left column
	    $this->Cell(16); 
	else if($this->getX() == 105) //right column
	    $this->Cell(-79);
        //Print date
        $this->Cell(10,10,date('d M Y'));
        //Move over
        $this->Cell(152);
        //Print page number
        $this->Cell(2,10,$this->PageNo());
        //Move some more
        $this->Cell(17);
        //Print number of pages
        $this->Cell(10,10,'{nb}');
    }
}

?>
