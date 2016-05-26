<?php

define('GPA_REPORT_PLEDGE', 1);
define('GPA_REPORT_MEMBER', 2);

PHPWS_Core::initModClass('sdr', 'Report.php');

class GreekGPAReportRegistration extends ReportRegistration
{
    protected function init()
    {
        $this->setName('Fraternity/Sorority GPA Report');
        $this->setClass('GreekGPAReport');
        $this->provideHtml();
        $this->providePdf();
    }
}

PHPWS_Core::requireInc('sdr', 'FPDF.php');

class GreekGPAReport
{
    var $data;
    var $term;

    var $greek_data;
    var $frat_data;
    var $sor_data;
    var $frat_pledge_data;
    var $sor_pledge_data;
    var $pledge_data;
    var $nphc_frat_data;
    var $nphc_sor_data;
    var $nphc_data;

    var $univ_data;

    function __construct($term) {
        PHPWS_Core::initCoreClass('Database.php');

        PHPWS_Core::initModClass('sdr', 'GPAController.php');
        $gpa = new GPAController();
	
        if(!$gpa->haveDataFor($term)) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('GPA Data for ' . Term::getPrintableSelectedTerm() . ' has not yet been loaded into ClubConnect.  Please contact ESS if you believe this to be in error.');
        }

        $this->term = $term;

        $this->greek_data       = new GreekGPAReportDatum();
        $this->frat_data        = new GreekGPAReportDatum();
        $this->sor_data         = new GreekGPAReportDatum();
        $this->frat_pledge_data = new GreekGPAReportDatum();
        $this->sor_pledge_data  = new GreekGPAReportDatum();
        $this->pledge_data      = new GreekGPAReportDatum();
        $this->nphc_frat_data   = new GreekGPAReportDatum();
        $this->nphc_sor_data    = new GreekGPAReportDatum();
        $this->nphc_data        = new GreekGPAReportDatum();

        PHPWS_Core::initModClass('sdr', 'SpecialGPA.php');
        $this->univ_data = new SpecialGPA(NULL, $term);

        $pledges = $this->getRawData($term, GPA_REPORT_PLEDGE);
        $this->organizeData($pledges);

        $members = $this->getRawData($term, GPA_REPORT_MEMBER);
        $this->organizeData($members);
    }

    function getRawData($term, $type)
    {
        $roleid = ($type == GPA_REPORT_PLEDGE ? "AND sdr_membership_role.role_id = '32'" : "");
        $result = PHPWS_DB::getAll(<<<SQL
SELECT
    sdr_member.id AS member_id,
    sdr_member.first_name,
    sdr_member.middle_name,
    sdr_member.last_name,
    sdr_student_registration.class,
    sdr_membership_role.role_id,
    sdr_gpa.cumgpa,
    sdr_gpa.semgpa,
    sdr_organization_full.id AS org_id,
    sdr_organization_full.name AS org_name,
    sdr_organization_full.type_id AS org_type
FROM sdr_membership
    LEFT JOIN sdr_organization_full
        ON sdr_membership.organization_id = sdr_organization_full.id
       AND sdr_membership.term = sdr_organization_full.term
    LEFT OUTER JOIN sdr_membership_role
        ON sdr_membership.id = sdr_membership_role.membership_id
    LEFT JOIN sdr_member
        ON sdr_membership.member_id = sdr_member.id
    LEFT JOIN sdr_student
        ON sdr_member.id = sdr_student.id
    LEFT JOIN sdr_student_registration
        ON sdr_student.id = sdr_student_registration.student_id
    LEFT JOIN sdr_gpa
        ON sdr_member.id = sdr_gpa.member_id
WHERE 
        sdr_membership.term                  = '$term'
    AND sdr_gpa.term                         = '$term'
    AND sdr_student_registration.term        = '$term'
    AND sdr_organization_full.type_id        IN ('11', '12', '100', '101')
    AND sdr_membership.student_approved      = '1'
    AND sdr_membership.organization_approved = '1'
    $roleid
ORDER BY sdr_organization_full.name, sdr_member.last_name, sdr_member.first_name, sdr_member.middle_name
SQL
);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    function organizeData($raw)
    {
        foreach($raw as $datum) {
            if(!isset($this->data[$datum['org_name']])) {
                $orgdata = new GreekGPAReportOrganization(
                    $datum['org_id'],
                    $datum['org_name'],
                    $datum['org_type']);
                $this->data[$datum['org_name']] = $orgdata;
            }

            $student = new GreekGPAReportStudent(
               $datum['member_id'],
                GreekGPAReportStudent::buildName(
                    $datum['first_name'],
                    $datum['middle_name'],
                    $datum['last_name']),
                $datum['class'],
                $datum['cumgpa'],
                $datum['semgpa'],
                $datum['role_id']);

            if($this->data[$datum['org_name']]->addStudent($student)) {
                $this->file($student, $this->data[$datum['org_name']]);
            }
        }
    }

    function file(GreekGPAReportStudent $student,
        GreekGPAReportOrganization $org)
    {
        // TODO: There's a hell of a lot of hard-coded shit in here.  Expose
        // an interface for updating these things and take out the constants.
        // Constants are bad, mmkay?

        // All Greeks
        $this->greek_data->add($student);

        if($student->status == 32) {    // Pledges

            // All Pledges
            $this->pledge_data->add($student);

            // Fraternity Pledges
            if($org->type == 11 || $org->type == 100) {
                $this->frat_pledge_data->add($student);
            }

            // Sorority Pledges
            if($org->type == 12 || $org->type == 101) {
                $this->sor_pledge_data->add($student);
            }

        } else {    // Members

            // Fraternity Members
            if(($org->type == 11 || $org->type == 100) && $student->status != 32) {
                $this->frat_data->add($student);
            }

            // Sorority Members
            if(($org->type == 12 || $org->type == 101) && $student->status != 32) {
                $this->sor_data->add($student);
            }

        }

        $nphc_f = array(253,257,269);
        if(in_array($org->id, $nphc_f)) {   // Determine if $org is NPHC Frat
            
            // All NPHC
            $this->nphc_data->add($student);

            // NPHC Fraternities
            $this->nphc_frat_data->add($student);

        }

        $nphc_s = array(271,271);
        if(in_array($org->id, $nphc_s)) {   // Determine if $org is NPHC Sor

            // All NPHC
            $this->nphc_data->add($student);

            // NPHC Sororities
            $this->nphc_sor_data->add($student);

        }
    }

    function renderHTML()
    {
        $tpl = new PHPWS_Template('sdr');
        $result = $tpl->setFile('report_html/greek_gpa.tpl');
        if(PHPWS_Error::logIfError($result)) {
            return "Template Error in renderHTML.";
        }

        ksort($this->data);

        foreach($this->data as $org) {
            $all_members = 0;
            foreach($org->members as $student) {
                $tpl->setCurrentBlock('ALL_MEMBER');
                $tpl->setData($student->getArray());
                $tpl->parseCurrentBlock();
                $all_members++;
            }

            $members = 0;
            foreach($org->members as $student) {
                if($student->status == 32) continue;  // No Pledges Here
                $tpl->setCurrentBlock('MEMBER');
                $tpl->setData($student->getArray());
                $tpl->parseCurrentBlock();
                $members++;
            }

            $pledges = 0;
            foreach($org->members as $student) {
                if($student->status != 32) continue;  // Only Pledges Here
                $tpl->setCurrentBlock('PLEDGE');
                $tpl->setData($student->getArray());
                $tpl->parseCurrentBlock();
                $pledges++;
            }

            $tpl->setCurrentBlock('ROSTER');
            $tpl->setData(array('ROSTER_HEADING' => $org->name,
				'AVGALLMEMSEM'   => $org->avg_gpa->calc_sem(),
                                'AVGALLMEMCUM'   => $org->avg_gpa->calc_cum(),
                                'ALLMEMCOUNT'    => $all_members,
                                'AVGMEMSEM'      => $org->avg_member_gpa->calc_sem(),
                                'AVGMEMCUM'      => $org->avg_member_gpa->calc_cum(),
                                'MEMCOUNT'       => $members,
                                'AVGPLESEM'      => $org->avg_pledge_gpa->calc_sem(),
                                'AVGPLECUM'      => $org->avg_pledge_gpa->calc_cum(),
                                'PLECOUNT'       => $pledges));
            $tpl->parseCurrentBlock();
        }

        GreekGPAReport::univWomenData($tpl);
        GreekGPAReport::univMenData($tpl);
        GreekGPAReport::univCombData($tpl);
        GreekGPAReport::summaryRow($tpl, 'Fraternity Average',               $this->frat_data);
        GreekGPAReport::summaryRow($tpl, 'Sorority Average',                 $this->sor_data);
        GreekGPAReport::summaryRow($tpl, 'Combined Average',                 $this->greek_data);
        GreekGPAReport::summaryRow($tpl, 'Fraternity Average (New Members)', $this->frat_pledge_data);
        GreekGPAReport::summaryRow($tpl, 'Sorority Average (New Members)',   $this->sor_pledge_data);
        GreekGPAReport::summaryRow($tpl, 'Combined Average (New Members)',   $this->pledge_data);
        GreekGPAReport::summaryRow($tpl, 'NPHC Fraternities',                $this->nphc_frat_data);
        GreekGPAReport::summaryRow($tpl, 'NPHC Sororities',                  $this->nphc_sor_data);
        GreekGPAReport::summaryRow($tpl, 'NPHC Combined',                    $this->nphc_data);

        Layout::addPageTitle('Fraternity/Sorority GPA Report - ' . Term::toString($this->term));
        return $tpl->get();
    }

    function univWomenData(PHPWS_Template $tpl)
    {
        $tpl->setCurrentBlock('SUMMARY_ROW');
        $tpl->setData(array(
            'HEADING' => 'All University Women',
            'SEMGPA'  => $this->univ_data->enrolled_previous_female,
            'CUMGPA'  => $this->univ_data->enrolled_cumulative_female));
        $tpl->parseCurrentBlock();
    }

    function univMenData(PHPWS_Template $tpl)
    {
        $tpl->setCurrentBlock('SUMMARY_ROW');
        $tpl->setData(array(
            'HEADING' => 'All University Men',
            'SEMGPA'  => $this->univ_data->enrolled_previous_male,
            'CUMGPA'  => $this->univ_data->enrolled_cumulative_male));
        $tpl->parseCurrentBlock();
    }

    function univCombData(PHPWS_Template $tpl)
    {
        $tpl->setCurrentBlock('SUMMARY_ROW');
        $tpl->setData(array(
            'HEADING' => 'All Students Combined',
            'SEMGPA'  => $this->univ_data->enrolled_previous_overall,
            'CUMGPA'  => $this->univ_data->enrolled_cumulative_overall));
        $tpl->parseCurrentBlock();
    }

    function summaryRow(PHPWS_Template $tpl, $title, GreekGPAReportDatum $data)
    {
        $tpl->setCurrentBlock('SUMMARY_ROW');
        $tpl->setData(array(
            'HEADING' => $title,
            'SEMGPA'  => $data->calc_sem(),
            'CUMGPA'  => $data->calc_cum()));
        $tpl->parseCurrentBlock();
    }

    function ff($f) // It means "float format" but I wanted something short
    {   
        if(is_null($f)) {
            return 'N/A*';
        } else {
            return sprintf("%1.3f",$f);
        }
    }

    function renderPDF()
    {
        $pdf = new GreekGPAReportPDF();
        $pdf->term = Term::toString($this->term);

        ksort($this->data);

        foreach($this->data as $org) {
          $page = $pdf->CreateMembersPage($org->name, 'All Members');

            $all_members = 0;
            foreach($org->members as $student) {
	      $page->addStudent($student);
	      $all_members++;
            }

            if($all_members > 0) {
                $page->setSemesterAverage($org->avg_gpa->calc_sem());
                $page->setCumulativeAverage($org->avg_gpa->calc_cum());
                $page->setCount($all_members);
                $page->render();
            }
  
	  $page = $pdf->CreateMembersPage($org->name, 'Continuing Members');

            $members = 0;
            foreach($org->members as $student) {
                if($student->status == 32) continue;    // No Pledges Here
                $page->addStudent($student);
                $members++;
            }

            if($members > 0) {
                $page->setSemesterAverage($org->avg_member_gpa->calc_sem());
                $page->setCumulativeAverage($org->avg_member_gpa->calc_cum());
                $page->setCount($members);
                $page->render();
            }

            $page = $pdf->CreateMembersPage($org->name, 'New Members');

            $pledges = 0;
            foreach($org->members as $student) {
                if($student->status != 32) continue;    // Only Pledges Here
                $page->addStudent($student);
                $pledges++;
            }

            if($pledges > 0) {
                $page->setSemesterAverage($org->avg_pledge_gpa->calc_sem());
                $page->setCumulativeAverage($org->avg_pledge_gpa->calc_cum());
                $page->setCount($pledges);
                $page->render();
            }
        }

        $pdf->summaryPage();
        $pdf->summaryRow('All University Women',  NULL,
            $this->univ_data->enrolled_previous_female,
            $this->univ_data->enrolled_cumulative_female);
        $pdf->summaryRow('All University Men',    NULL,
            $this->univ_data->enrolled_previous_male,
            $this->univ_data->enrolled_cumulative_male);
        $pdf->summaryRow('All Students Combined', NULL,
            $this->univ_data->enrolled_previous_overall,
            $this->univ_data->enrolled_cumulative_overall);
        $pdf->summaryRow('Fraternity Average (Continuing Members)', $this->frat_data);
        $pdf->summaryRow('Sorority Average (Continuing Members)',   $this->sor_data);
        $pdf->summaryRow('Combined Average (Continuing Members)',   $this->greek_data);
        $pdf->summaryRow('Fraternity Average (New Members)',        $this->frat_pledge_data);
        $pdf->summaryRow('Sorority Average (New Members)',          $this->sor_pledge_data);
        $pdf->summaryRow('Combined Average (New Members)',          $this->pledge_data);
        $pdf->summaryRow('NPHC Fraternities',                       $this->nphc_frat_data);
        $pdf->summaryRow('NPHC Sororities',                         $this->nphc_sor_data);
        $pdf->summaryRow('NPHC Combined',                           $this->nphc_data);

        $pdf->output();

        exit(0);
    }
}

class GreekGPAReportPDF extends FPDF
{
    var $currentTitle;
    var $term;

    function Header()
    {
        $this->setFont('Arial', 'B', 15);
        $this->Cell(0, 15*POINT, 'Fraternity/Sorority GPA Report', 0, 0, 'L');
        $this->SetX(0);
        $page = parent::PageNo();
        $this->Cell(0, 15*POINT, "$this->term - Page $page", 0, 1, 'R');
        $this->Ln(10*POINT);

        if(isset($this->currentTitle)) {
            $this->addTitle($this->currentTitle);

            $this->SetFont('Courier', 'B', 12);
            $this->nameCell('Name');
            $this->classCell('Class');
            $this->curCell('Current');
            $this->cumCell('Cumulative');
        }
    }

    function Footer()
    {
        $this->setFont('Arial', 'I', 10);
        $this->SetY(282);
        $this->MultiCell(0, 10*POINT, "*Note: Students with 'N/A' in the Current Semester column were involved in Study Abroad, Student Teaching, or another academic activity that does not grant a grade.");
    }

    function CreateMembersPage($title, $type)
    {
        $page = new GreekGPAReportPDFMembersPage($this, $title, $type);
        return $page;
    }

    function nameCell($string, $fill = FALSE)
    {
        $this->Cell(117, 12*POINT, $string, 'B', 0, 'L', $fill);
    }

    function classCell($string, $fill = FALSE)
    {
        $this->Cell(24, 12*POINT, $string, 'B', 0, 'C', $fill);
    }
    
    function curCell($string, $fill = FALSE)
    {
        $this->Cell(24, 12*POINT, $string, 'B', 0, 'C', $fill);
    }

    function cumCell($string, $fill = FALSE)
    {
        $this->Cell(24, 12*POINT, $string, 'B', 1, 'C', $fill);
    }

    function averageLine($sem, $cum)
    {
        $this->SetFont('Courier', 'B', 12);
        $this->Cell(141, 12*POINT, 'Average:', 'B', 0, 'L');
        $this->curCell($sem);
        $this->cumCell($cum);
    }

    function countLine($count)
    {
        $this->SetFont('Courier', 'B', 12);
        $this->Cell(100, 12*POINT, 'Total Count: ' . $count, '', 0, 'L');
    }

    function addTitle($text)
    {
        $this->SetFont('Courier', 'B', 16);
        $this->Cell(0, 16*POINT, $text, 0, 1, 'C');
        $this->Ln(10*POINT);
    }

    function summaryPage()
    {
        unset($this->currentTitle);
        $this->AddPage();
        $this->addTitle('UNIVERSITY TOTALS');

        $this->Ln(15*POINT);

        $this->SetFont('Courier', 'B', 12);
        $this->Cell(100, 12*POINT, '', 'B');
        $this->Cell(24, 12*POINT, 'Current', 'B', 0, 'C');
        $this->Cell(24, 12*POINT, 'Cumulative', 'B', 1, 'C');
    }

    function summaryRow($name, $datum, $sem = NULL, $cum = NULL)
    {
        if(!is_null($datum)) {
            $sem = $datum->calc_sem();
            $cum = $datum->calc_cum();
        }

        $this->SetFont('Courier', 'B', 12);
        $this->Cell(100, 12*POINT, $name, 'TB', 0, 'L');

        $this->SetFont('Courier', '', 12);
        $this->Cell(24, 12*POINT, $sem, 'TB', 0, 'C');
        $this->Cell(24, 12*POINT, $cum, 'TB', 1, 'C');
    }
}

class GreekGPAReportPDFMembersPage
{
    var $pdf;
    var $title;
    var $type;
    var $students;
    var $semavg;
    var $cumavg;
    var $count;

    function __construct($pdf, $title, $type)
    {
        $this->pdf   = $pdf;
        $this->title = $title;
        $this->type  = $type;
    }

    function addStudent($student)
    {
        $this->students[] = $student;
    }

    function setSemesterAverage($avg)
    {
        $this->semavg = $avg;
    }

    function setCumulativeAverage($avg)
    {
        $this->cumavg = $avg;
    }

    function setCount($count)
    {
        $this->count = $count;
    }

    function render()
    {
        $pdf = $this->pdf;
        $pdf->currentTitle = "$this->title ($this->type)";
        $pdf->AddPage();

        $pdf->SetFont('Courier', NULL, 12);
        foreach($this->students as $student) {
            $pdf->nameCell($student->name);
            $pdf->classCell($student->class);
            if(is_null($student->semgpa)) {
                $pdf->curCell('N/A*');
            } else {
                $pdf->curCell($student->semgpa);
            }
            $pdf->cumCell($student->cumgpa);
        }

        $pdf->averageLine($this->semavg, $this->cumavg);
        $pdf->countLine($this->count);
    }
}

class GreekGPAReportOrganization
{
    var $id;
    var $name;
    var $type;
    var $members;
    var $avg_gpa;
    var $avg_member_gpa;
    var $avg_pledge_gpa;

    function __construct($id, $name, $type)
    {
        $this->id   = $id;
        $this->name = $name . ' ' .
            ($type == 11 || $type == 100 ? 'Fraternity' :
            ($type == 12 || $type == 101 ? 'Sorority' : 'Uncategorized'));
        $this->type = $type;
        $this->members = array();
        $this->avg_gpa = new GreekGPAReportDatum();
        $this->avg_member_gpa = new GreekGPAReportDatum();
        $this->avg_pledge_gpa = new GreekGPAReportDatum();
    }

    function addStudent($student)
    {
        if($this->alreadyCounted($student))
            return FALSE;

        $this->members[] = $student;
        $this->avg_gpa->add($student);
        if($student->status == 32) {
            $this->avg_pledge_gpa->add($student);
        } else {
            $this->avg_member_gpa->add($student);
        }

        return TRUE;
    }

    function alreadyCounted($student)
    {
        foreach($this->members as $member) {
            if($member->id == $student->id)
                return TRUE;
        }

        return FALSE;
    }
}

class GreekGPAReportStudent
{
    var $id;
    var $name;
    var $cumgpa;
    var $semgpa;
    var $status;
    var $class;

    function __construct($id, $name, $class, $cumgpa, $semgpa, $status)
    {
        $this->id     = $id;
        $this->name   = $name;
        $this->class  = $class;
        $this->cumgpa = $cumgpa;
        $this->semgpa = $semgpa;
        $this->status = $status;
    }

    function buildName($f, $m, $l)
    {
        return "$l, $f $m";
    }

    function getArray()
    {
        return array('NAME'  => $this->name,
                     'CLASS' => $this->class,
                     'SEM'   => GreekGPAReport::ff($this->semgpa),
                     'CUM'   => GreekGPAReport::ff($this->cumgpa));
    }
}

class GreekGPAReportDatum
{
    var $cumulative_count;
    var $cumulative_total;
    var $semester_count;
    var $semester_total;

    function __construct()
    {
        $cumulative_count = 0;
        $cumulative_total = 0.0;
        $semester_count = 0;
        $semester_total = 0.0;
    }

    function add(GreekGPAReportStudent $student)
    {
        $this->cumulative_count++;
        $this->cumulative_total += $student->cumgpa;
        if(!is_null($student->semgpa)) {
            $this->semester_count++;
            $this->semester_total += $student->semgpa;
        }
    }

    function calc_sem()
    {
        if($this->semester_count == 0) return GreekGPAReport::ff(0);
        return GreekGPAReport::ff(round(1000 * $this->semester_total / $this->semester_count) / 1000);
    }

    function calc_cum()
    {
        if($this->cumulative_count == 0) return GreekGPAReport::ff(0);
        return GreekGPAReport::ff(round(1000 * $this->cumulative_total / $this->cumulative_count) / 1000);
    }
}

