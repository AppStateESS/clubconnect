<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::requireInc('sdr', 'FPDF.php');

class OrganizationRegistrationPDF extends FPDF
{
    protected $reg;
    protected $or;
    protected $settings;

    public function __construct($reg, $or, $settings)
    {
        $this->reg = $reg;
        $this->or = $or;
        $this->settings = $settings;

        parent::__construct();
    }

    function Header() {
        $s = $this->settings->get();
        $r = $this->reg;
        $o = $this->or;

        // Set the document font
        $this->SetFont($s['header_font'], $s['header_weight'], $s['header_font_size']);

        // Move to starting point
        $this->SetXY($s['header_x'], $s['header_y']);

        // Print Title
        $this->Cell($s['title_width'], $s['cell_height'], $r['fullname'] . ' (' . $r['shortname'] . ')');
    }

    function Footer() {
        $s = $this->settings->get();

        // Move to starting point
        $this->SetXY($s['footer_x'], $s['footer_y']);

        // Get the center of the page
        $center = (int) $this->w / 2;

        // Left Cell (printed date)
        $this->Cell($center, $s['cell_height'], 'Printed on ' . date('M/d/Y'));

        // Right Cell (pages)
        $this->Cell($center, $s['cell_height'], 'Page ' . $this->PageNo() . ' of {nb}');
    }
}

?>
