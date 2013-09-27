<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Report.php');

class AnnualReportRegistration extends ReportRegistration
{
	protected function init()
	{
		$this->setName('Annual Report');
		$this->setClass('AnnualReport');
		$this->provideHtml();
		$this->providePdf();
	}
}

PHPWS_Core::requireInc('sdr', 'FPDF.php');

class AnnualReport
{
	var $data;
	var $term;

	var $orgs;
	var $dl;
	var $cl;
	var $emp;
	var $ncaa;
	var $total;

	function __construct($term) {
		PHPWS_Core::initCoreClass('Database.php');

		$this->term = $term;

		$this->orgs  = $this->getOrgData($term);
		$this->dl    = $this->getDlData($term);
		$this->cl    = $this->getClData($term);
		$this->emp   = $this->getEmpData($term);
		$this->ncaa  = $this->getNcaaData($term);

		$this->total = $this->annualReportSum();
	}

	function getOrgData($term)
	{
		$db = new PHPWS_DB('sdr_membership');
		$db->addJoin('left',
                     'sdr_membership',
                     'sdr_organization_full',
                     'organization_id',
                     'id');
		$db->addJoin('left',
                     'sdr_membership',
                     'sdr_student',
                     'member_id',
                     'id');
		$db->addColumn('sdr_student.gender');
        $db->addWhere('sdr_membership.term', $term);
        $db->addWhere('sdr_organization_full.term', $term);
		$db->addWhere('sdr_organization_full.type_id', 14, '!=');
		$db->addGroupBy('sdr_student.gender');
		$result = $db->select('count_array');

		return AnnualReportValues::constructFromDB($result);
	}

	function getDlData($term)
	{
		$db = new PHPWS_DB('sdr_deans_chancellors_lists');
		$db->addJoin('left',
                     'sdr_deans_chancellors_lists',
                     'sdr_student',
                     'member_id',
                     'id');
		$db->addColumn('sdr_student.gender');
		$db->addWhere('sdr_deans_chancellors_lists.term', $term);
		$db->addWhere('sdr_deans_chancellors_lists.d_c_list', 'Dean\'s List');
		$db->addGroupBy('sdr_student.gender');
		$result = $db->select('count_array');

		return AnnualReportValues::constructFromDB($result);
	}

	function getClData($term)
	{
		$db = new PHPWS_DB('sdr_deans_chancellors_lists');
		$db->addJoin('left',
                     'sdr_deans_chancellors_lists',
                     'sdr_student',
                     'member_id',
                     'id');
		$db->addColumn('sdr_student.gender');
		$db->addWhere('sdr_deans_chancellors_lists.term', $term);
		$db->addWhere('sdr_deans_chancellors_lists.d_c_list', 'Chancellor\'s List');
		$db->addGroupBy('sdr_student.gender');
		$result = $db->select('count_array');

		return AnnualReportValues::constructFromDB($result);
	}

	function getEmpData($term)
	{
		$db = new PHPWS_DB('sdr_employments');
		$db->addJoin('left',
                     'sdr_employments',
                     'sdr_student',
                     'member_id',
                     'id');
		$db->addColumn('sdr_student.gender');
		$db->addWhere('sdr_employments.term', $term);
		$db->addGroupBy('sdr_student.gender');
		$result = $db->select('count_array');

		return AnnualReportValues::constructFromDB($result);
	}

	function getNcaaData($term)
	{
		$db = new PHPWS_DB('sdr_membership');
		$db->addJoin('left',
                     'sdr_membership',
                     'sdr_organization_full',
                     'organization_id',
                     'id');
		$db->addJoin('left',
                     'sdr_membership',
                     'sdr_student',
                     'member_id',
                     'id');
		$db->addColumn('sdr_student.gender');
		$db->addWhere('sdr_membership.term', $term);
		$db->addWhere('sdr_organization_full.type_id', 14);
		$db->addGroupBy('sdr_student.gender');
		$result = $db->select('count_array');

		return AnnualReportValues::constructFromDB($result);
	}

	function annualReportSum()
	{
		$m = 0;
		$f = 0;

		$m += $this->orgs->getMale();
		$f += $this->orgs->getFemale();

		$m += $this->dl->getMale();
		$f += $this->dl->getFemale();

		$m += $this->cl->getMale();
		$f += $this->cl->getFemale();

		$m += $this->emp->getMale();
		$f += $this->emp->getFemale();

		$m += $this->ncaa->getMale();
		$f += $this->ncaa->getFemale();

		return new AnnualReportValues($m, $f);
	}

	function renderHTML()
	{
		$tpl = new PHPWS_Template('sdr');
		$result = $tpl->setFile('report_html/annual.tpl');
		if(PHPWS_Error::logIfError($result)) {
			return "Template Error in renderHTML.";
		}

		self::summaryRow($tpl, 'Clubs/Organization Records', $this->orgs);
		self::summaryRow($tpl, 'Dean\'s List Records', $this->dl);
		self::summaryRow($tpl, 'Chancellor\'s List Records', $this->cl);
		self::summaryRow($tpl, 'Employment Records', $this->emp);
		self::summaryRow($tpl, 'Intercollegiate Athletics Records', $this->ncaa);
		self::summaryRow($tpl, 'Total SDR Records', $this->total);

		$tpl->setData(array('TERM' => Term::toString($this->term)));

        Layout::addPageTitle('Annual Report - ' . Term::toString($this->term));
		return $tpl->get();
	}

	function summaryRow(PHPWS_Template $tpl, $title, AnnualReportValues $values)
	{
		$tpl->setCurrentBlock('SUMMARY_ROW');
		$tpl->setData(array(
            'HEADING' => $title,
            'MALE'    => $values->getMale(),
            'FEMALE'  => $values->getFemale(),
            'TOTAL'   => $values->getTotal()));
		$tpl->parseCurrentBlock();
	}

	function renderPDF()
	{
		$pdf = new AnnualReportPDF();

		$pdf->setTerm(Term::toString($this->term));
		$pdf->summaryRow('Club/Organization Records', $this->orgs);
		$pdf->summaryRow('Dean\'s List Records', $this->dl);
		$pdf->summaryRow('Chancellor\'s List Records', $this->cl);
		$pdf->summaryRow('Employment Records', $this->emp);
		$pdf->summaryRow('Intercollegiate Athletics Records', $this->ncaa);
		$pdf->summaryRow('Total SDR Records', $this->total);

		$pdf->output();

		exit(0);
	}
}

class AnnualReportValues
{
	var $male;
	var $female;

	function __construct($m, $f)
	{
		$this->male   = $m;
		$this->female = $f;
	}

	function getMale()
	{
		return $this->male;
	}

	function getFemale()
	{
		return $this->female;
	}

	function getTotal()
	{
		return $this->male + $this->female;
	}

	function constructFromDB($result)
	{
		if(PHPWS_Error::logIfError($result)) {
			test($result,1);
		}

		$f = 0;
		$m = 0;

		foreach($result as $datum) {
			switch($datum['gender']) {
				case 'F':
					$f = $datum['count'];
					break;
				case 'M':
					$m = $datum['count'];
					break;
				default:
                    // TODO: Exception here?
					echo "Unknown Gender {$datum['gender']}";
			}
		}

		return new AnnualReportValues($m, $f);
	}
}

class AnnualReportPDF extends FPDF
{

	function setTerm($term)
	{
		$heading = "Annual Report Data - $term";

		$this->AddPage();

		$this->SetFont('Courier', 'B', 16);
		$this->Cell(0, 16*POINT, $heading, 0, 1, 'C');
		$this->Ln(15*POINT);

		$this->SetFont('Courier', 'B', 12);
		$this->Cell(100, 12*POINT, '', 'B');
		$this->Cell(24, 12*POINT, 'Male',   'BL', 0, 'C');
		$this->Cell(24, 12*POINT, 'Female', 'BL', 0, 'C');
		$this->Cell(24, 12*POINT, 'Total',  'BL', 1, 'C');
	}

	function summaryRow($name, $values)
	{
		$m = $values->getMale();
		$f = $values->getFemale();
		$t = $values->getTotal();

		$this->SetFont('Courier', 'B', 12);
		$this->Cell(100, 12*POINT, $name, 'TB', 0, 'L');

		$this->SetFont('Courier', '', 12);
		$this->Cell(24, 12*POINT, $m, 'TBL', 0, 'R');
		$this->Cell(24, 12*POINT, $f, 'TBL', 0, 'R');
		$this->Cell(24, 12*POINT, $t, 'TBL', 1, 'R');
	}

}

?>
