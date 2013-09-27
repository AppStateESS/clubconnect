#!/usr/bin/php
<?php

$db = pg_connect("dbname=sdr user=jtickle");

pg_query($db, "BEGIN");

$sql = "select distinct member_id, term from sdr_membership join sdr_organization on sdr_membership.organization_id = sdr_organization.child order by term, member_id";
$membership_result = pg_query($db, $sql);

$parents = array();
while($membership = pg_fetch_assoc($membership_result)) {
    $i = $membership['member_id'];
    $t = $membership['term'];

    $sql = "select organization_id from sdr_membership where member_id=$i and term=$t";
    $org_result = pg_query($db, $sql);
    $orgs = pg_fetch_all_columns($org_result, 0);

    $neworgs = array();
    foreach($orgs as $org) {
        $parent = getParent($org, $db);
        echo "MEMBER $i TERM $t: Converting $org to $parent...  ";

        if(in_array($parent, $neworgs)) {
            echo "COLLISION! $parent\n";
            $parents[] = array('parent' => $parent, 'term' => $t);
            continue;
        }

        echo "SUCCESS\n";

        $neworgs[] = $parent;
    }
}

foreach($parents as $parent) {
    $o = $parent['parent'];
    $t = $parent['term'];

    echo "Deleting oid $o term $t... ";
    $sql = "DELETE FROM sdr_membership WHERE organization_id={$parent['parent']} AND term={$parent['term']}";
    $result = pg_query($db, $sql);
    $c = pg_affected_rows($result);
    echo "$c rows affected.\n";
}

pg_query($db, "COMMIT");

function getParent($orgid, $db)
{
    static $cache;
    if(is_null($cache)) $cache = array();

    if(isset($cache[$orgid])) return $cache[$orgid];

    $sql = "SELECT id FROM sdr_organization WHERE child=$orgid";
    $result = pg_query($db, $sql);

    $numrows = pg_num_rows($result);

    if($numrows > 1) ohShit("DANGER WILL ROBINSON: $orgid has more than one parent!", $db);

    if($numrows < 1) {
        $cache[$orgid] = $orgid;
        return $orgid;
    }

    $parent = pg_fetch_result($result, 0, 0);
    $cache[$orgid] = $parent;
    return $parent;
}

function ohShit($string, $db)
{
    echo "$string\n";
    pg_query($db, "ROLLBACK");
    pg_close($db);
    exit(1);
}

?>
