<?php

PHPWS_Core::initModClass('sdr', 'Report.php');

class MulticulturalGPAReportRegistration extends ReportRegistration
{
    protected function init()
    {
        $this->setName('Multicultural Organization GPA Report');
        $this->setClass('MulticulturalGPAReport');
        $this->provideHtml();
        $this->providePdf();
    }
}

PHPWS_Core::requireInc('sdr', 'FPDF.php');

class MulticulturalGPAReport
{
    var $data;
    var $term;

    var $msd_data;

    var $univ_data;

    function __construct($term) {
        PHPWS_Core::initCoreClass('Database.php');

        $this->term = $term;

        $this->msd_data         = new MulticulturalGPAReportDatum();

        PHPWS_Core::initModClass('sdr', 'SpecialGPA.php');
        $this->univ_data = new SpecialGPA(NULL, $term);

        $members = $this->getRawData($term);
        $this->organizeData($members);
    }

    function getRawData($term)
    {
        $ids = '(' . implode(', ', array(
            383,    // Hillel
            398,    // Pagan Student Association
            469,    // Ladies Elite
            474,    // Order of Black and Gold
            543,    // Asian Student Association
            647,    // Appalachian African Community
            501,    // ASU Native American Council
            909,    // Entropy Dance Crew
            540,    // Hip Hop Oasis
            883,    // Hispanic Student Association
            773,    // Hmong Society Club
            833,    // Minority Men's Leadership Circle
            505,    // Minority Women's Leadership Circle
            758,    // Sexuality and Gender Alliance (SAGA)
            427,    // Transaction
            449,    // Black Student Association
            881,    // Women's Center
            962,    // Muslim Student Association
            39,     // Korean Culture Club
            42      // Japanese Culture Club
        )) . ')';
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
    sdr_organization_full.name AS org_name
FROM sdr_membership
    LEFT JOIN sdr_organization_full
        ON sdr_membership.organization_id = sdr_organization_full.id
       AND sdr_membership.term = sdr_organization_full.term
    LEFT JOIN sdr_membership_role
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
    AND sdr_organization_full.term           = '$term'
    AND sdr_organization_full.id        IN $ids
    AND sdr_membership.student_approved      = '1'
    AND sdr_membership.organization_approved = '1'
    AND sdr_membership_role.role_id != 53
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
                $orgdata = new MulticulturalGPAReportOrganization(
                    $datum['org_id'],
                    $datum['org_name']);
                $this->data[$datum['org_name']] = $orgdata;
            }

            $student = new MulticulturalGPAReportStudent(
               $datum['member_id'],
                MulticulturalGPAReportStudent::buildName(
                    $datum['first_name'],
                    $datum['middle_name'],
                    $datum['last_name']),
                $datum['class'],
                $datum['cumgpa'],
                $datum['semgpa']);

            if($this->data[$datum['org_name']]->addStudent($student)) {
                $this->file($student, $this->data[$datum['org_name']]);
            }
        }
    }

    function file(MulticulturalGPAReportStudent $student,
        MulticulturalGPAReportOrganization $org)
    {
        $this->msd_data->add($student);
    }

    function renderHTML()
    {
        $tpl = new PHPWS_Template('sdr');
        $result = $tpl->setFile('report_html/msd_gpa.tpl');
        if(PHPWS_Error::logIfError($result)) {
            return "Template Error in renderHTML.";
        }

        ksort($this->data);

        foreach($this->data as $org) {
            $members = 0;
            foreach($org->members as $student) {
                $tpl->setCurrentBlock('MEMBER');
                $tpl->setData($student->getArray());
                $tpl->parseCurrentBlock();
                $members++;
            }

            $tpl->setCurrentBlock('ROSTER');
            $tpl->setData(array('ROSTER_HEADING' => $org->name,
                                'AVGMEMSEM'      => $org->avg_gpa->calc_sem(),
                                'AVGMEMCUM'      => $org->avg_gpa->calc_cum(),
                                'MEMCOUNT'       => $members));
            $tpl->parseCurrentBlock();
        }

        MulticulturalGPAReport::univCombData($tpl);
        MulticulturalGPAReport::summaryRow($tpl, 'Multicultural Organization Average',                 $this->msd_data);

        Layout::addPageTitle('Multicultural Organization GPA Report - ' . Term::toString($this->term));
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

    function summaryRow(PHPWS_Template $tpl, $title, MulticulturalGPAReportDatum $data)
    {
        $tpl->setCurrentBlock('SUMMARY_ROW');
        $tpl->setData(array(
            'HEADING' => $title,
            'SEMGPA'  => $data->calc_sem(),
            'CUMGPA'  => $data->calc_cum()));
        $tpl->parseCurrentBlock();
    }

    static function ff($f) // It means "float format" but I wanted something short
    {   
        if(is_null($f)) {
            return 'N/A*';
        } else {
            return sprintf("%1.3f",$f);
        }
    }

    function renderPDF()
    {
        $pdf = new MulticulturalGPAReportPDF();
        $pdf->term = Term::toString($this->term);

        ksort($this->data);

        foreach($this->data as $org) {
            $page = $pdf->CreateMembersPage($org->name, 'Members');

            $members = 0;
            foreach($org->members as $student) {
                $page->addStudent($student);
                $members++;
            }

            if($members > 0) {
                $page->setSemesterAverage($org->avg_gpa->calc_sem());
                $page->setCumulativeAverage($org->avg_gpa->calc_cum());
                $page->setCount($members);
                $page->render();
            }
        }

        $pdf->summaryPage();
        $pdf->summaryRow('All Students Combined', NULL,
            $this->univ_data->enrolled_previous_overall,
            $this->univ_data->enrolled_cumulative_overall);
        $pdf->summaryRow('Multicultural Organization Average', $this->msd_data);

        $pdf->output();

        exit(0);
    }
}

class MulticulturalGPAReportPDF extends FPDF
{
    var $currentTitle;
    var $term;

    function Header()
    {
        $this->setFont('Arial', 'B', 15);
        $this->Cell(0, 15*POINT, 'Multicultural Organization GPA Report', 0, 0, 'L');
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
        $page = new MulticulturalGPAReportPDFMembersPage($this, $title, $type);
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

class MulticulturalGPAReportPDFMembersPage
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

class MulticulturalGPAReportOrganization
{
    var $id;
    var $name;
    var $members;
    var $avg_gpa;

    function __construct($id, $name)
    {
        $this->id   = $id;
        $this->name = $name;
        $this->members = array();
        $this->avg_gpa = new MulticulturalGPAReportDatum();
    }

    function addStudent($student)
    {
        if($this->alreadyCounted($student))
            return FALSE;

        $this->members[] = $student;
        $this->avg_gpa->add($student);

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

class MulticulturalGPAReportStudent
{
    var $id;
    var $name;
    var $cumgpa;
    var $semgpa;
    var $status;
    var $class;

    function __construct($id, $name, $class, $cumgpa, $semgpa)
    {
        $this->id     = $id;
        $this->name   = $name;
        $this->class  = $class;
        $this->cumgpa = $cumgpa;
        $this->semgpa = $semgpa;
    }

    static function buildName($f, $m, $l)
    {
        return "$l, $f $m";
    }

    function getArray()
    {
        return array('NAME'  => $this->name,
                     'CLASS' => $this->class,
                     'SEM'   => MulticulturalGPAReport::ff($this->semgpa),
                     'CUM'   => MulticulturalGPAReport::ff($this->cumgpa));
    }
}

class MulticulturalGPAReportDatum
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

    function add(MulticulturalGPAReportStudent $student)
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
        if($this->semester_count == 0) return MulticulturalGPAReport::ff(0);
        return MulticulturalGPAReport::ff(round(1000 * $this->semester_total / $this->semester_count) / 1000);
    }

    function calc_cum()
    {
        if($this->cumulative_count == 0) return MulticulturalGPAReport::ff(0);
        return MulticulturalGPAReport::ff(round(1000 * $this->cumulative_total / $this->cumulative_count) / 1000);
    }
}

