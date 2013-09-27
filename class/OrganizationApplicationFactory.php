<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');

class OrganizationApplicationFactory
{
    public static function getApplicationsByUserId($userid, $term)
    {
        $db = self::initDb();
        self::whereAnyUserId($db, $userid);
        self::whereTerm($db, $term);
        self::wherePending($db);
        $result = self::select($db);

        return self::plugApplications($result);
    }

    // TODO: one of these that only shows fully confirmed applications.
    public static function getAllPendingApplicationsAdminFirst()
    {
        $result = PHPWS_DB::getAll("select * from sdr_organization_application WHERE (admin_confirmed IS NULL OR pres_confirmed IS NULL OR advisor_confirmed IS NULL) order by (admin_confirmed is not null), created_on");

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('Could not select Organization Applications.');
        }

        return self::plugApplications($result);
    }

    protected static function initDb()
    {
        $db = new PHPWS_DB('sdr_organization_application');
        $db->addColumn('sdr_organization_application.*');
        return $db;
    }

    protected static function select(&$db)
    {
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('Could not select Organization Applications.');
        }

        return $result;
    }

    protected static function plugApplications(array $result)
    {
        $apps = array();

        foreach($result as $r) {
            // TODO: Join user, president, advisor
            if(!isset($apps[$r['id']])) {
                $apps[$r['id']] = new OrganizationApplication($r);
            }
        }

        return $apps;
    }

    protected static function adminOrder(&$db)
    {
        $db->addOrder('(admin_confirmed > 0)');
    }

    protected static function whereAnyUserId(&$db, $userid)
    {
        $db->addWhere('user_id', $userid, NULL, 'OR', 'UID');
        $db->addWhere('req_pres_id', $userid, NULL, 'OR', 'UID');
        $db->addWhere('req_advisor_id', $userid, NULL, 'OR', 'UID');
    }

    protected static function whereTerm(&$db, $term)
    {
        $db->addWhere('term', $term);
    }

    protected static function wherePending(&$db)
    {
        $db->addWhere('admin_confirmed', null, null, 'OR', 'PENDING');
        $db->addWhere('pres_confirmed', null, null, 'OR', 'PENDING');
        $db->addWhere('advisor_confirmed', null, null, 'OR', 'PENDING');
    }
}

?>
