<?php

require_once('../../conf/config.php');
require_once('../../class/Settings.php');

require_once('DB.php');
require_once('../../../../config/core/config.php');

$dsn = DB::parseDSN(PHPWS_DSN);
//print_r($dsn);

$numcreate = 500;
$minper    = 0;
$maxper    = 15;
$orgs      = array();
$semesters = array(SDR_SEMESTER_SPRING,SDR_SEMESTER_FALL,SDR_SEMESTER_SUMMER1,SDR_SEMESTER_SUMMER2);
$years     = array();
$fnames    = array("John","Bob","James","Burt","Ed","Lafawnduh","Shaquanda",
                    "Bacon","Sunshine","Matt","Trey","Daniel",
                    "Sarah","Amy","Emily","Smelly");
$member_status = array(23, 1, 2, 4, 34, 10, 22, 25, 27, 30, 15);
$lnames    = array("Smith","Jones","Brown");
$members_index = 0;
$membership_index = 0;
$new_members = array();

echo "Members Populator 0.2\n";
echo "Connecting to database...\n";

mysql_connect($dsn['hostspec'],$dsn['username'],$dsn['password']);
mysql_select_db($dsn['database']);
echo mysql_error();


echo "Gathering organizations... ";
$results = mysql_query("select id from sdr_settings_organizations");
echo mysql_error();
while($row = mysql_fetch_assoc($results)) {
    $orgs[] = $row['id'];
}
$members_index = 1;
$membership_index = 1;

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'sdr_members_seq'"))==1) {
    $members_index = mysql_query("select Id from sdr_members_seq");
    $members_index = mysql_fetch_array($members_index);
    $members_index = $members_index[0];
    $members_index++;

} 

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'sdr_membership_seq'"))==1) {
    $membership_index = mysql_query("select Id from sdr_membership_seq");
    $membership_index = mysql_fetch_array($membership_index);
    $membership_index = $membership_index[0];
    $membership_index++;
} 

echo (count($orgs) . " found\n\n");
for($i = date('Y'); $i >= 1991; $i--) {
    $years[] = $i;
}

echo '<br /><br />';
for($i = 0,$k = 0; $i < $numcreate; $i++) {
    echo '<br />';
    $fname = getRandom($fnames);
    $lname = getRandom($lnames);
    $time = time();

    echo "User $members_index: $lname, $fname";

    $sql = "insert into
sdr_members(id,student_id,first_name,last_name,label,created,updated,hidden,locked,deleted,approved) values($members_index, '245678989', '$fname', '$lname','auto-generated',$time,$time,0,0,0,1)";
    mysql_query($sql);

    $error = mysql_error();

    if(!empty($error))
        echo '<br /><div style="margin-left:20px;">'.$error.'</div>';
    else {
        $new_members[] = $members_index;
        $members_index++;

    }

    $per = mt_rand($minper,$maxper);
    for($j = 0; $j < $per; $j++,$k++) {
        $org   = getRandom($orgs);
        $sem   = getRandom($semesters);
        $year  = getRandom($years);
        $day   = 1;
        $month = 1;

        switch($sem) {
        case SDR_SEMESTER_SPRING:
            $month = date('m', SDR_SEMESTER_SPRING_MS);
            $day = date('d', SDR_SEMESTER_SPRING_MS);
            break;
        case SDR_SEMESTER_SUMMER1:
            $month = date('m', SDR_SEMESTER_SUMMER1_MS);
            $day = date('d', SDR_SEMESTER_SUMMER1_MS);
            break;
        case SDR_SEMESTER_SUMMER2:
            $month = date('m', SDR_SEMESTER_SUMMER2_MS);
            $day   = date('d', SDR_SEMESTER_SUMMER2_MS);
            break;
        case SDR_SEMESTER_FALL:
            $month = date('m', SDR_SEMESTER_FALL_MS);
            $day   = date('d', SDR_SEMESTER_FALL_MS);
            break;
        }
        
        $timestamp = mktime(0,0,0, $month, $day, $year);
        
        mysql_query("insert into
sdr_membership(id,member_id,organization,semester,year,timestamp,locked,deleted,hidden)
values($membership_index,".getRandom($new_members).",$org,'$sem','$year',$timestamp,0,0,0)");
        $error = mysql_error();
        if(!empty($error))
            echo '<br /><div style="margin-left:20px;">'.$error.'</div>';
        else {
            $status = getRandom($member_status);            
            mysql_query("insert into
sdr_membership_member_status(membership_id,member_status)
values($membership_index, $status)");            
            
            $error = mysql_error();
            if(!empty($error)) {
                echo '<br /><div style="margin-left:20px;">'.$error.'</div>';
            }

            $membership_index++;
        }

    }

}

if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'sdr_members_seq'"))!=1) {
    mysql_query("CREATE TABLE `sdr_members_seq` (
                `id` int(10) unsigned NOT NULL auto_increment,
                PRIMARY KEY  (`id`)
                ) TYPE=MyISAM AUTO_INCREMENT=4;");
    mysql_query('INSERT INTO `sdr_members_seq` (id) values (0);');
}


if(mysql_num_rows(mysql_query("SHOW TABLES LIKE 'sdr_membership_seq'"))!=1) {
    mysql_query("CREATE TABLE `sdr_membership_seq` (
                `id` int(10) unsigned NOT NULL auto_increment,
                PRIMARY KEY  (`id`)
                ) TYPE=MyISAM AUTO_INCREMENT=4;");
    mysql_query('INSERT INTO `sdr_membership_seq` (id) values (0);');
}


mysql_query("update sdr_members_seq set id={$members_index}");
mysql_query("update sdr_membership_seq set id={$membership_index}");

echo "<br /><br />Members: $i  Memberships: $k\n\n";

function getRandom($arr)
{
    return $arr[rand(0,count($arr)-1)];
}

?>