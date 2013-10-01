<?php

PHPWS_Core::requireInc('sdr', 'FPDF.php');
PHPWS_Core::initModClass('sdr', 'TranscriptPrintSettings.php');

class TranscriptPDF extends FPDF
{
    protected $name;
    protected $sid;
    protected $settings;

    public function __construct($name, $sid, $settings)
    {
        $this->name     = $name;
        $this->sid      = $sid;
        $this->settings = $settings;

        parent::__construct();
    }

    function Header() {
        $stgs = $this->settings->get();

        //Select Arial bold 15
        $this->SetFont('Arial','B',$stgs['std_font_size']);
        //get to the correct starting point....
        $this->SetY($stgs['start_y']);
        //Move to the right
        $this->Cell($stgs['start_x']);
        //Title
        $this->Cell($stgs['name_width'],$stgs['cell_height'],$this->name);
        //Move more to the right
        $this->Cell($stgs['sid_x_offset']);
        //Some random text
        $this->Cell($stgs['sid_width'],$stgs['cell_height'],$this->sid);
        //Line break
        $this->Ln($stgs['body_y_offset']);
    }

    function Footer() {
        $stgs = $this->settings->get();

        //align for paper
        $this->SetXY($stgs['foot_x'], $stgs['foot_y']);
        //Select Arial Bold 14
        $this->SetFont('Arial','B',$stgs['std_font_size']);
        //Print date
        $this->Cell($stgs['date_width'],$stgs['cell_height'],date('d M Y'));
        //Move over
        $this->Cell($stgs['pn_x_offset']);
        //Print page number
        $this->Cell($stgs['pn_width'],10,$this->PageNo());
        //Move some more
        $this->Cell($stgs['of_x_offset']);
        //Print number of pages
        $this->Cell($stgs['of_width'],$stgs['cell_height'],'{nb}');
    }
}

?>
