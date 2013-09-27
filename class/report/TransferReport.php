<?php

PHPWS_Core::initModClass('sdr', 'Report.php');
require_once(PHPWS_SOURCE_DIR . 'mod/sdr/inc/libchart/classes/libchart.php');
define('TRANSFER_REPORT_LOCATION', PHPWS_SOURCE_DIR . 'files/sdr/transfer_');

class TransferReportRegistration extends ReportRegistration
{
    protected function init()
    {
        $this->setName('Transfer Involvement Report');
        $this->setClass('TransferReport');
        $this->provideHtml();
        $this->providePdf();
    }
}

class TransferReport
{
    private $numNonTransfer;
    private $numTransfer;
    private $typeTotals;
    private $typeFreq;
    private $typeSexTotals;
    private $typeSexFreq;
    private $typeEthnTotals;
    private $typeEthnFreq;

    function __construct() {
        PHPWS_Core::initModClass('sdr', 'report/Transfer/CountStudentsByTypeByTermLoader.php');
        $counter = new CountStudentsByTypeByTermLoader(0,Term::getSelectedTerm());
        $this->numNonTransfer = $counter->load();
        $counter = new CountStudentsByTypeByTermLoader(1,Term::getSelectedTerm());
        $this->numTransfer = $counter->load();

        PHPWS_Core::initModClass('sdr', 'report/Transfer/StudentDistributionByTypeByTermLoader.php');
        $distribution = new StudentDistributionByTypeByTermLoader(0,Term::getSelectedTerm());
        $this->distNonTransfer = $distribution->load();
        $distribution = new StudentDistributionByTypeByTermLoader(1,Term::getSelectedTerm());
        $this->distTransfer = $distribution->load();

        test(array($this->distNonTransfer, $this->distTransfer), 1);


        $this->establishTemporaryTable();
        $this->initTypeFreqData();
        $this->initTypeSexFreqData();
        $this->initTypeEthnFreqData();

        $this->createTransferDistributionChart('transfer_dist');
        $this->createNonTransferDistributionChart('nontransfer_dist');
        $this->createTypeHistogram('type_histogram');
    }

    private function establishTemporaryTable()
    {
        $sql = <<<SQL
CREATE TEMPORARY TABLE transfer_report_data AS
SELECT DISTINCT
    sdr_student.id,
    sdr_student_registration.type,
    sdr_student.gender AS sex,
    sdr_student.ethnicity
FROM sdr_student
    JOIN sdr_student_registration
        ON sdr_student.id = sdr_student_registration.student_id
WHERE
    sdr_student.id > 899999999 AND
    (sdr_student_registration.type = 'T' OR sdr_student_registration.type = 'F')
SQL;

        $result = PHPWS_DB::getAll($sql);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('Could not create temporary table transfer_report_data.');
        }
    }

    private function initTypeFreqData()
    {
        $sql = <<<SQL
SELECT
    COUNT(*),
    sdr_membership.term,
    students.id AS id,
    CASE type
        WHEN 'F' THEN 'FRESHMAN'
        WHEN 'T' THEN 'TRANSFER'
        ELSE 'UNKNOWN'
    END AS type
FROM sdr_membership
    JOIN transfer_report_data AS students
        ON sdr_membership.member_id = students.id
WHERE sdr_membership.term % 100 in (10,40)
GROUP BY students.id, term, type, sex, ethnicity
SQL;

        $result = PHPWS_DB::getAll($sql);

        // Create Frequency Table
        $freq = array();
        $totals = array();
        foreach($result as $r) {
            if(!isset($totals[$r['type']])) {
                $totals[$r['type']] = 1;
            } else {
                $totals[$r['type']]++;
            }

            if(!isset($freq[$r['type']][$r['count']])) {
                $freq[$r['type']][$r['count']] = 1;
            } else {
                $freq[$r['type']][$r['count']]++;
            }
        }

        // Normalize Frequency Table as Percentages of Total
        foreach($freq as $t => $more) {
            foreach($more as $k => $v) {
                $freq[$t][$k] = $v / $totals[$t];
            }
        }

        // Build libchart Data
        $chart = new VerticalBarChart(1000,500);
        $fdata = new XYDataSet();
        $tdata = new XYDataSet();
        for($i = 1; $i < max(count($freq['FRESHMAN']), count($freq['TRANSFER'])); $i++) {
            if(isset($freq['FRESHMAN'][$i])) {
                $fdata->addPoint(new Point($i, round($freq['FRESHMAN'][$i],2)));
            } else {
                $fdata->addPoint(new Point($i, 0));
            }

            if(isset($freq['TRANSFER'][$i])) {
                $tdata->addPoint(new Point($i, round($freq['TRANSFER'][$i],2)));
            } else {
                $tdata->addPoint(new Point($i, 0));
            }
        }

        $data = new XYSeriesDataSet();
        $data->addSerie("Non-Transfer", $fdata);
        $data->addSerie("Transfer", $tdata);
        $chart->setDataSet($data);
        $chart->setTitle("Herpin Derp");
        $chart->render("/var/www/html/sdr/files/sdr/derp.png");
        exit();

        $this->typeTotals = $totals;
        $this->typeFreq = $freq;
    }

    private function initTypeSexFreqData()
    {
        $sql = <<<SQL
SELECT
    COUNT(*),
    sdr_membership.term,
    students.id AS id,
    CASE type
        WHEN 'F' THEN 'FRESHMAN'
        WHEN 'T' THEN 'TRANSFER'
        ELSE 'UNKNOWN'
    END AS type,
    CASE sex
        WHEN 'F' THEN 'FEMALE'
        WHEN 'M' THEN 'MALE'
        ELSE 'UNKNOWN'
    END AS sex
FROM sdr_membership
    JOIN transfer_report_data AS students
        ON sdr_membership.member_id = students.id
WHERE sdr_membership.term % 100 in (10,40)
GROUP BY students.id, term, type, sex
SQL;

        $result = PHPWS_DB::getAll($sql);

        $freq = array();
        $totals = array();
        foreach($result as $r) {
            if(!isset($totals[$r['type']][$r['sex']])) {
                $totals[$r['type']][$r['sex']] = 1;
            } else {
                $totals[$r['type']][$r['sex']]++;
            }

            if(!isset($freq[$r['type']][$r['sex']][$r['count']])) {
                $freq[$r['type']][$r['sex']][$r['count']] = 1;
            } else {
                $freq[$r['type']][$r['sex']][$r['count']]++;
            }
        }

        // Normalize Frequency Table as Percentages of Total
        foreach($freq as $t => $more) {
            foreach($more as $s => $moar) {
                foreach($moar as $c => $v) {
                    $freq[$t][$s][$c] = $v / $totals[$t][$s];
                }
            }
        }

        $this->typeSexTotals = $totals;
        $this->typeSexFreq = $freq;
    }

    private function initTypeEthnFreqData()
    {
        $sql = <<<SQL
SELECT
    COUNT(*),
    sdr_membership.term,
    students.id AS id,
    CASE type
        WHEN 'F' THEN 'FRESHMAN'
        WHEN 'T' THEN 'TRANSFER'
        ELSE 'UNKNOWN'
    END AS type,
    CASE ethnicity
        WHEN 'I' THEN 'American Indian or Alaskan Native'
        WHEN 'O' THEN 'Asian / Asian American'
        WHEN 'B' THEN 'Black / African American'
        WHEN 'W' THEN 'Caucasian / White'
        WHEN 'N' THEN 'Not Specified'
        WHEN 'H' THEN 'Hispanic'
        WHEN 'C' THEN 'Cuban American'
        WHEN 'M' THEN 'Mexican American'
        WHEN 'P' THEN 'Puerto Rican American - US'
        WHEN 'R' THEN 'Puerto Rican American - PR'
        WHEN 'X' THEN 'Multiracial'
        ELSE 'UNKNOWN'
    END AS ethnicity
FROM sdr_membership
    JOIN transfer_report_data AS students
        ON sdr_membership.member_id = students.id
WHERE sdr_membership.term % 100 in (10,40)
GROUP BY students.id, term, type, ethnicity
SQL;

        $result = PHPWS_DB::getAll($sql);

        $freq = array();
        $totals = array();
        foreach($result as $r) {
            if(!isset($totals[$r['type']][$r['ethnicity']])) {
                $totals[$r['type']][$r['ethnicity']] = 1;
            } else {
                $totals[$r['type']][$r['ethnicity']]++;
            }

            if(!isset($freq[$r['type']][$r['ethnicity']][$r['count']])) {
                $freq[$r['type']][$r['ethnicity']][$r['count']] = 1;
            } else {
                $freq[$r['type']][$r['ethnicity']][$r['count']]++;
            }
        }

        // Normalize Frequency Table as Percentages of Total
        foreach($freq as $t => $more) {
            foreach($more as $e => $moar) {
                foreach($moar as $c => $v) {
                    $freq[$t][$e][$c] = $v / $totals[$t][$e];
                }
            }
        }

        $this->typeEthnTotals = $totals;
        $this->typeEthnFreq = $freq;
    }

    private function createTransferDistributionChart($filename)
    {
        $t = $this->typeFreq['TRANSFER'];
        $data = new XYDataSet();
        for($i = 1; $i < count($t); $i++) {
            if(isset($t[$i])) {
                $data->AddPoint(new Point($i, $t[$i]));
            } else {
                $data->AddPoint(new Point($i, 0));
            }
        }

        $chart = new VerticalBarChart(1000,500);
        $chart->setDataSet($data);
        $chart->setTitle('Transfer Membership Frequency');
            $chart->render(TRANSFER_REPORT_LOCATION . $filename . '.png');
    }

    private function createNonTransferDistributionChart($filename)
    {
    }

    private function createTypeHistogram($filename)
    {
        $freq = $this->typeFreq;

        $chart = new VerticalBarChart(1000,500);
        $fdata = new XYDataSet();
        $tdata = new XYDataSet();
    }
}

?>
