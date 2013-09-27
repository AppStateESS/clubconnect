#!/usr/bin/php
<?php

/**
 * Switches the student_id column with the id column iff student_id looks like a banner id.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

define('TEMPBEFORE', 'sdr_conv_temp_before.dat');
define('TEMPAFTER',  'sdr_conv_temp_after.dat');
define('PRE',  0);
define('POST', 1);
define('UNDER_BID', 899999999);
define('OVER_BID', 1000000000);

// PLEASE NOTE: THIS IS SUPER IMPORTANT.
// Log in with the actual SDR username and password here, otherwise the table permissions
// will be all screwed up and you'll probably only find out the hard way.
$db = pg_connect('dbname=sdr_dev host=localhost user=sdr_dev password=sdr_dev');

query($db, 'BEGIN');

// These are the old tables that will be backed up and then updated
$oldtables = array(
    'sdr_deans_chancellors_lists',
    'sdr_employments',
    'sdr_gpa',
    'sdr_membership',
    'sdr_scholarship',
    'sdr_transcript_requests',
    'sdr_orgn_request_info');

// This is used to store the IDs that will be changing.  (Not all of them will.)
$conversion = array();

  ///////////////////////////////////////////////////////////////////////////
 // Create the new Member, Student, Address, Registration, Advisor tables //
///////////////////////////////////////////////////////////////////////////

$sql = "
    CREATE TABLE sdr_member (
        id INTEGER NOT NULL,
        username VARCHAR(30),
        prefix VARCHAR(20),
        first_name VARCHAR(30),
        middle_name VARCHAR(30),
        last_name VARCHAR(60),
        suffix VARCHAR(20),
        advisor SMALLINT NOT NULL DEFAULT 0,
        PRIMARY KEY(id)
    )";

query($db, $sql);

$sql = "
    CREATE TABLE sdr_student (
        id INTEGER NOT NULL,
        oldid INTEGER,
        gender CHARACTER(1) NOT NULL,
        ethnicity CHARACTER(1) NOT NULL,
        birthdate DATE,
        citizen CHARACTER(1),
        date_enrolled DATE,
        PRIMARY KEY(id)
    )";

query($db, $sql);

$sql = "
    CREATE TABLE sdr_address (
        id INTEGER NOT NULL,
        student_id INTEGER NOT NULL,
        type CHARACTER(2) NOT NULL,
        sequence SMALLINT NOT NULL,
        line_one VARCHAR(60) NOT NULL,
        line_two VARCHAR(60),
        line_three VARCHAR(60),
        city VARCHAR(20) NOT NULL,
        county VARCHAR(5) NOT NULL,
        state CHARACTER(2) NOT NULL,
        zipcode CHARACTER(5) NOT NULL,
        phone VARCHAR(20),
        PRIMARY KEY(id)
    )";

query($db, $sql);

$sql = "
    CREATE SEQUENCE sdr_address_seq";

query($db, $sql);

$sql = "
    CREATE TABLE sdr_student_registration (
        id INTEGER NOT NULL,
        student_id INTEGER NOT NULL,
        term INTEGER NOT NULL,
        type CHARACTER(1) NOT NULL,
        level CHARACTER(2) NOT NULL,
        class CHARACTER(2),
        PRIMARY KEY(id)
    )";

query($db, $sql);

$sql = "
    CREATE TABLE sdr_advisor (
        id INTEGER NOT NULL,
        home_phone VARCHAR(20),
        office_phone VARCHAR(20),
        cell_phone VARCHAR(20),
        office_location VARCHAR(255),
        department VARCHAR(255),
        PRIMARY KEY(id)
    )";

query($db, $sql);

   ///////////////////////////////////////////////////////////////////
  // These queries will be used to insert data into the new tables //
 // and update data in the old.                                   //
///////////////////////////////////////////////////////////////////

$INSERT_MEMBER  = "INSERT INTO sdr_member  VALUES($1, $2, $3, $4, $5, $6, $7, $8)";
$INSERT_STUDENT = "INSERT INTO sdr_student VALUES($1, $2, $3, $4, $5, $6, $7)";
$INSERT_ADDRESS = "INSERT INTO sdr_address VALUES(nextval('sdr_address_seq'),
    $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)";

$UPDATE_OLD = "UPDATE $1 SET member_id=$2 WHERE member_id=$3";

  /////////////////////////
 // Unlock "Old" Tables //
/////////////////////////
echo "Unlocking old data tables...\n";

echo "\tsdr_deans_chancellors_lists\n";

query($db, "ALTER TABLE sdr_employments DROP CONSTRAINT member_id_fk");
echo "\tsdr_employments\n";

echo "\tsdr_gpa\n";

query($db, "ALTER TABLE sdr_membership DROP CONSTRAINT member_id_fk");
echo "\tsdr_membership\n";

query($db, "ALTER TABLE sdr_scholarship DROP CONSTRAINT member_id_fk");
echo "\tsdr_scholarship\n";

echo "\tsdr_transcript_requests\n";
echo "\tsdr_orgn_request_info\n";

    ////////////////////////////////////////////////////////////////////
   // Apparently, some fuckwad what can't write an update script dun //
  // gon went and provided us some god damn duplicate usernames.    //
 // Make sure all of the usernames are unique before we proceed.   //
////////////////////////////////////////////////////////////////////

$sql = "
    SELECT id, student_id, asu_username
    FROM sdr_members
    WHERE asu_username in
        (SELECT asu_username FROM sdr_members GROUP BY asu_username HAVING count(*) = 2)
    AND asu_username != ''
    ORDER BY asu_username";

$result = query($db, $sql);

echo "Attempting to resolve any duplicate records...\n";

// These should always be in pairs; if not, error'd!
while($row = pg_fetch_assoc($result))
{
    $username  = trim($row['asu_username']);
    $first_id  = $row['id'];
    $first_sid = $row['student_id'];

    $row = pg_fetch_assoc($result);

    if(!isset($row['asu_username']) || $username != trim($row['asu_username'])) {
        echo "Pair checking failed in the duplicate fixer: $username, {$row['asu_username']}\n";
        fail($db);
    }

    $second_id  = $row['id'];
    $second_sid = $row['student_id'];

    if($first_sid > UNDER_BID && $second_sid < UNDER_BID) {
        $bannerid = $first_sid;
        $killid = $second_id;
    } else if($first_sid < UNDER_BID && $second_sid > UNDER_BID) {
        $bannerid = $second_sid;
        $killid = $first_id;
    } else {
        echo "Could not determine which record to use:  $first_id or $second_id\n";
        fail($db);
    }

    $conversion[$killid] = $bannerid;

    $sql = "UPDATE sdr_members SET deleted=1 WHERE id=$killid";
    query($db, $sql);

    echo "DUPLICATE RECORD!  Save: $bannerid; Kill: $killid\n";
}

   //////////////////////////////////////////////////////////////
  // Load all basic Member data into new Member table and     //
 // others (see above), also initialize the conversion array //
//////////////////////////////////////////////////////////////
echo "Reogranizing Basic Member Data...\n";

$sql = "SELECT * FROM sdr_members WHERE deleted=0";
$result = query($db, $sql);

$totalrows = pg_num_rows($result);
$count = 0;

while($row = pg_fetch_assoc($result)) {
    progress(++$count, $totalrows);

    // Some "Student IDs" are not numeric.  This doesn't make sense,
    // so dump 'em.
    if(!is_numeric($row['student_id'])) {
        $row['student_id'] = OVER_BID;
    }

    // If the Student ID is a Banner ID, we're using that as their
    // SDR ID.
    if($row['student_id'] > UNDER_BID && $row['student_id'] < OVER_BID) {
        $conversion[$row['id']] = $row['student_id'];
        $temp = $row['id'];
        $row['id'] = $row['student_id'];
        $row['student_id'] = $temp;
    }

    // Sanitize Data
    
    if(empty($row['asu_username'])) {
        $row['asu_username'] = NULL;
    }

    if(empty($row['gender']) || is_null($row['gender'])) {
        $row['gender'] = 'X';
    }

    switch($row['race']) {
        case 'African American':
        case 'Black':
        case 'B':
            $row['race'] = 'B';
            break;
        case 'American Indian or Alaskan Native':
        case 'I':
            $row['race'] = 'I';
            break;
        case 'Caucasian':
        case 'White':
        case 'W':
            $row['race'] = 'W';
            break;
        case 'Cuban':
        case 'C':
            $row['race'] = 'C';
            break;
        case 'Hispanic':
        case 'Mexican':
        case 'M':
        case 'H':
        case 'Puerto Rican':
        case 'P':
            $row['race'] = 'H';
            break;
        case 'Other':
        case 'O':
            $row['race'] = 'O';
            break;
        case 'N':
            $row['race'] = 'N';
            break;
        default:
            $row['race'] = 'X';
            break;
    }

    switch($row['year_in_school']) {
        case 'Graduate':
        case 'GR':
            $row['level'] = 'G';
            $row['class'] = NULL;
            break;
        case 'freshman':
        case 'Freshman':
        case 'FR':
            $row['level'] = 'U';
            $row['class'] = 'FR';
            break;
        case 'sophomore':
        case 'Sophomore':
        case 'SO':
            $row['level'] = 'U';
            $row['class'] = 'SO';
            break;
        case 'Junior':
        case 'JR':
            $row['level'] = 'U';
            $row['class'] = 'JR';
            break;
        case 'Senior':
        case 'SR':
            $row['level'] = 'U';
            $row['class'] = 'SR';
            break;
        case 'DR':
            $row['level'] = 'D';
            $row['class'] = NULL;
            break;
        case 'NC':
        case 'ND':
        case 'NU':
        case 'UV':
        case 'VI':
            $row['level'] = 'U';
            $row['class'] = $row['year_in_school'];
            break;
        default:
            $row['level'] = 'XX';
            $row['class'] = 'XX';
            break;
    }


    // Insert appropriate data into sdr_member
    $member = array(
        $row['id'],
        $row['asu_username'],
        NULL,
        $row['first_name'],
        $row['middle_name'],
        $row['last_name'],
        NULL,
        0);
    query($db, $INSERT_MEMBER, $member);

    // Insert appropriate data into sdr_student
    $student = array(
        $row['id'],
        $row['student_id'],
        $row['gender'],
        $row['race'],
        NULL,
        'X',
        NULL);
    query($db, $INSERT_STUDENT, $student);

    // Insert ASU BOX into sdr_address
    if(!empty($row['asu_box']) && !is_null($row['asu_box'])) {
        $address = array(
            $row['id'],
            'AB',
            0,
            $row['asu_box'],
            NULL, NULL, 'Boone', 'WA', 'NC', '28608', NULL);
        query($db, $INSERT_ADDRESS, $address);
    }

    // Insert Primary Address into sdr_address
    if(!empty($row['pmnt_addr_line_1']) && !is_null($row['pmnt_addr_line_1'])) {
        $address = array(
            $row['id'],
            'PR',
            0,
            $row['pmnt_addr_line_1'],
            $row['pmnt_addr_line_2'],
            NULL,
            $row['pmnt_city'],
            'XX',
            substr($row['pmnt_state'], 0, 2),
            substr($row['pmnt_zip'], 0, 5),
            NULL);
        query($db, $INSERT_ADDRESS, $address);
    }

    // Insert Secondary Address into sdr_address
    if(!empty($row['lcl_addr_line_1']) && !is_null($row['lcl_addr_line_1'])) {
        $address = array(
            $row['id'],
            'PR',
            0,
            $row['lcl_addr_line_1'],
            $row['lcl_addr_line_2'],
            NULL,
            $row['lcl_city'],
            'XX',
            substr($row['lcl_state'], 0, 2),
            substr($row['lcl_zip'], 0, 5),
            NULL);
        query($db, $INSERT_ADDRESS, $address);
    }
}

$convcount = count($conversion);
echo "\nReorganization complete.  $convcount conversions will be necessary.\n";

  /////////////////////////
 // Backup "Old" Tables //
/////////////////////////
echo "Backing up old data tables...\n";
foreach($oldtables as $table) {
    query($db, "SELECT * INTO old_$table FROM $table");
    echo "\t$table\n";
}

  /////////////////////////////////////////////////////////////
 // Apply the Member ID conversions to existing SDR records //
/////////////////////////////////////////////////////////////
echo "Applying conversions...\n";

$count = 0;
$updated = 0;
foreach($conversion as $oldid => $newid) {
    progress(++$count, $convcount);
    // We can reuse this, by golly!
    $conv = array($oldid, $newid);

    foreach($oldtables as $oldtable) {
        $updated += pg_affected_rows(query($db, "UPDATE $oldtable SET member_id=$2 WHERE member_id=$1", $conv));
    }
}

echo "\nSDR Database Converted.  $updated records updated.\n";

// TODO: Move Advisor Records

  //////////////////////////////////////////////
 // Lock down newly moved around data tables //
//////////////////////////////////////////////
echo "Locking down data tables...\n";

query($db, 'ALTER TABLE sdr_student ADD FOREIGN KEY (id) REFERENCES sdr_member(id)');
echo "\tsdr_student\n";

query($db, 'ALTER TABLE sdr_advisor ADD FOREIGN KEY (id) REFERENCES sdr_member(id)');
echo "\tsdr_advisor\n";

query($db, 'ALTER TABLE sdr_address ADD FOREIGN KEY (student_id) REFERENCES sdr_student(id)');
echo "\tsdr_address\n";

query($db, 'ALTER TABLE sdr_student_registration ADD FOREIGN KEY (student_id) REFERENCES sdr_student(id)');
echo "\tsdr_student_registration\n";

query($db, 'ALTER TABLE sdr_deans_chancellors_lists ADD FOREIGN KEY (member_id) REFERENCES sdr_student(id)');
echo "\tsdr_deans_chancellors_lists\n";

query($db, 'ALTER TABLE sdr_employments ADD FOREIGN KEY (member_id) REFERENCES sdr_student(id)');
echo "\tsdr_employments\n";

query($db, 'ALTER TABLE sdr_gpa ADD FOREIGN KEY (member_id) REFERENCES sdr_student(id)');
echo "\tsdr_gpa\n";

query($db, 'ALTER TABLE sdr_membership ADD FOREIGN KEY (member_id) REFERENCES sdr_member(id)');
echo "\tsdr_membership\n";

query($db, 'ALTER TABLE sdr_scholarship ADD FOREIGN KEY (member_id) REFERENCES sdr_student(id)');
echo "\tsdr_scholarship\n";

query($db, 'ALTER TABLE sdr_transcript_requests ADD FOREIGN KEY (member_id) REFERENCES sdr_student(id)');
echo "\tsdr_transcript_requests\n";

query($db, 'ALTER TABLE sdr_orgn_request_info ADD FOREIGN KEY (member_id) REFERENCES sdr_student(id)');
echo "\tsdr_orgn_request_info\n";

  ///////////////////////
 // Verify Everything //
///////////////////////
echo "Verifying all this stuff we did...\n";

foreach($oldtables as $oldtable) {
    $result = pg_query($db, "
        SELECT
            $oldtable.id,
            $oldtable.member_id as newid,
            old_$oldtable.member_id as oldid
        FROM
            $oldtable
        FULL OUTER JOIN old_$oldtable
            ON $oldtable.id = old_$oldtable.id
        LEFT OUTER JOIN sdr_member
            ON $oldtable.member_id = sdr_member.id
        LEFT OUTER JOIN sdr_members
            ON old_$oldtable.member_id = sdr_members.id
        WHERE
            sdr_member.id IS NULL OR
            sdr_members.id IS NULL OR
            sdr_member.username != sdr_members.asu_username
            ");

    $numrows = pg_num_rows($result);
    if($numrows > 0) {
        while($row = pg_fetch_assoc($result)) {
            print_r($row);
        }

        echo "\n\nError on $oldtable: pg_num_rows was $numrows\n";
        fail($db);
    }
}
  ///////////////////////////
 // Set up Sequence Table //
///////////////////////////
$result = query($db, 'SELECT max(id) FROM sdr_member WHERE id <= $1', array(UNDER_BID));
$row = pg_fetch_array($result);
$max = $row[0] + 1;

query($db, "CREATE SEQUENCE sdr_member_seq START WITH $max");

  //////////////////
 // Fix Advisors //
//////////////////
$result = query($db, "SELECT sdr_advisors.*, username FROM sdr_advisors LEFT OUTER JOIN sdr_member on asu_login = username WHERE deleted=0");

while($row = pg_fetch_assoc($result)) {
    if(is_null($row['username'])) {
        $r = pg_fetch_array(query($db, "SELECT nextval('sdr_member_seq')"));
        $id = $r[0];

        $advisor = array(
            $id,
            $row['asu_login'],
            NULL,
            $row['name_first'],
            $row['name_middle'],
            $row['name_last'],
            NULL,
            1);

        query($db, $INSERT_MEMBER, $advisor);
    } else {
        $r = pg_fetch_array(query($db, "SELECT id FROM sdr_member WHERE username=$1", array($row['asu_login'])));
        $id = $r[0];
    }

    $advisor = array(
        $id,
        $row['phone_home'],
        $row['phone_office'],
        $row['phone_cell'],
        $row['address'],
        $row['department']);

    query($db, "INSERT INTO sdr_advisor VALUES($1, $2, $3, $4, $5, $6)", $advisor);
}
  //////////////////////////////////
 // Fix Organization Permissions //
//////////////////////////////////
query($db, 'ALTER TABLE sdr_membership ADD COLUMN administrator SMALLINT NOT NULL DEFAULT 0');

$result = query($db, '
    SELECT
        sdr_organization_permission.*,
        sdr_member.id AS member_id,
        sdr_membership.id AS membership_id
    FROM sdr_organization_permission
    JOIN sdr_member ON asu_username = username
    LEFT OUTER JOIN sdr_membership ON organization_id = organization
        AND sdr_organization_permission.term = sdr_membership.term
        AND sdr_member.id = sdr_membership.member_id
    ORDER BY sdr_organization_permission.id');

while($row = pg_fetch_assoc($result)) {
    if(is_null($row['membership_id'])) {
        $values = array(
            $row['member_id'],
            $row['organization_id'],
            mktime(), null, 0, 0, 0, 1, null, 0, null, 1,
            $row['term'], 1);
        query($db, "INSERT INTO sdr_membership VALUES(nextval('sdr_membership_seq'),
            $1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)", $values);
    } else {
        query($db, "UPDATE sdr_membership SET administrator=1 WHERE id=$1", array($row['membership_id']));
    }
}

  ///////////
 // DONE! //
///////////

echo "Process Complete!  Please go back through and make sure everything\n";
echo "makes sense and this script didn't fuck it all up too bad.\n";

query($db, 'COMMIT');

exit(0);

 // The End.
/////////////////////////////////////

function query($db, $sql, $params = NULL)
{
    if(is_null($params))
        $result = pg_query($db, $sql);
    else
        $result = pg_query_params($db, $sql, $params);

    if($result === FALSE) {
        echo "An error has occurred.\n\nSQL: $sql\n\nParams:\n";
        print_r($params);
        fail($db);
    }

    return $result;
}

function progress($count, $total)
{
    echo "\r$count of $total (" . round($count / $total * 100) . "%) " . substr('/-\\|', $count%400 / 100, 1);
}

function fail($db)
{
    pg_query($db, 'ROLLBACK');
    exit(1);
}

/** Doesn't Work. */
function upfuckDump($db, $which)
{
    $file = ($which === PRE ? TEMPBEFORE : TEMPAFTER);
    $fp = fopen($file, 'w');

    if($fp === FALSE) {
        echo "Could not open $file for writing.\n";
        exit(1);
    }

    $prepost = ($which === PRE ? 'pre' : 'post');
    echo "Selecting $prepost-upfuck comparison data into $file...\n";

    $sql = "
        SELECT
            sdr_members.id as sdr_id,
            sdr_members.student_id as banner_id,
            sdr_deans_chancellors_lists.id as dc_id,
            sdr_employments.id as employments_id,
            sdr_gpa.id as gpa_id,
            sdr_membership.id as membership_id,
            sdr_scholarship.id as scholarship_id,
            sdr_transcript_requests.id as tr_id,
            sdr_orgn_request_info.id as ori_id
        FROM sdr_members
            LEFT OUTER JOIN sdr_deans_chancellors_lists
                ON sdr_deans_chancellors_lists.member_id = sdr_members.id
            LEFT OUTER JOIN sdr_employments
                ON sdr_employments.member_id = sdr_members.id
            LEFT OUTER JOIN sdr_gpa
                ON sdr_gpa.member_id = sdr_members.id
            LEFT OUTER JOIN sdr_membership
                ON sdr_membership.member_id = sdr_members.id
            LEFT OUTER JOIN sdr_scholarship
                ON sdr_scholarship.member_id = sdr_members.id
            LEFT OUTER JOIN sdr_transcript_requests
                ON sdr_transcript_requests.member_id = sdr_members.id
            LEFT OUTER JOIN sdr_orgn_request_info
                ON sdr_orgn_request_info.member_id = sdr_members.id
        ORDER BY
            sdr_members.id,
            sdr_members.student_id,
            sdr_deans_chancellors_lists.id,
            sdr_employments.id,
            sdr_gpa.id,
            sdr_membership.id,
            sdr_scholarship.id,
            sdr_transcript_requests.id,
            sdr_orgn_request_info.id
            ";

    $result = query($db, $sql);

    fputcsv($fp, array('SDRID', 'BANNERID', 'DC', 'EMPLOYMENT', 'GPA', 'MEMBERSHIP', 'SCHOLARSHIP', 'TR', 'ORI'));
    while($row = pg_fetch_assoc($result)) {
        if($which === POST) {
            if($row['sdr_id'] > 899999999 && $row['sdr_id'] < 1000000000) {
                $temp = $row['sdr_id'];
                $row['sdr_id'] = $row['banner_id'];
                $row['banner_id'] = $temp;
            }
        }

        fputcsv($fp, $row);
    }

    fclose($fp);
}

?>
