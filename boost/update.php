<?php
/**
 * @author Micah Carter <mcarter at tux dot appstate dot edu>
 **/

function sdr_update(&$content,$currentVersion) {
    switch ($currentVersion) {
        case version_compare($currentVersion, '0.6.2', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.2.sql');
            if (PHPWS_Error::logIfError($result)) {
                return $result;
            }

            $content[] = '+ Added transcript_email column to sdr_settings_control';
        case version_compare($currentVersion, '0.6.3','<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.3.sql');
            if (PHPWS_Error::logIfError($result)) {
                return $result;
            }
            $files = array();
            $files[] = 'templates/transcript/search_for_student.tpl';
            $files[] = 'templates/transcript/show_requests.tpl';
            PHPWS_Boost::updateFiles($files,'sdr');

            $content[] = '+ Fixed Transcript Print display';
            $content[] = '+ Fixed Transcript Request Search';
        case version_compare($currentVersion, '0.6.4', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.4.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.6.5', '<'):
        	$db = new PHPWS_DB;
        	$result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.5.sql');
        	if(PHPWS_Error::logIfError($result)) {
        		return $result;
        	}
        case version_compare($currentVersion, '0.6.6', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.6.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.6.7', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.7.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.6.8', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.8.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.6.9', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.9.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.6.10', '<'):
        	$db = new PHPWS_DB;
        	$result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.10.sql');
        	if(PHPWS_Error::logIfError($result)) {
        		return $result;
        	}
        case version_compare($currentVersion, '0.6.11', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.11.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.6.12', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.6.12.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.7.0', '<'):
        	$db = new PHPWS_DB;
        	if(!$db->inDatabase('sdr_student')) {
        		$content[] = '*****';
        		$content[] = 'Please run inc/bannerify.php before upgrading to 0.7.0.  If upgrading from <0.6.12, please manually reset the SDR version to 0.6.12 before trying again.';
                $content[] = '*****';
        		return FALSE;
        	}
        case version_compare($currentVersion, '0.7.1', '<'):
        	$db = new PHPWS_DB;
        	$result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.7.1.sql');
        	if(PHPWS_Error::logIfError($result)) {
        		return $result;
        	}
        case version_compare($currentVersion, '0.7.2', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.7.2.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.7.3', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.7.3.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.7.4', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.7.4.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.8.0', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.8.0.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.9.0', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.9.0.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.9.1', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.9.1.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.9.2', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.9.2.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.9.3', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.9.3.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.9.6', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.9.6.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.9.7', '<'):
            PHPWS_Core::initModClass('users', 'Permission.php');
            Users_Permission::registerPermissions('sdr', $content);
        case version_compare($currentVersion, '0.9.8', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.9.8.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.9.9', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.9.9.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.9.10', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.9.10.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
        case version_compare($currentVersion, '0.10.0', '<'):
            $content[] = "I hate to do this, but you can't upgrade to 0.10.0 using Boost.  Please refer to the 0.10.0.sql update file and good luck!";
            return false;

        case version_compare($currentVersion, '0.10.1', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.10.1.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }
            
        case version_compare($currentVersion, '0.10.2', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.10.2.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }

        case version_compare($currentVersion, '0.10.3', '<'):
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.10.3.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }

        case version_compare($currentVersion, '0.10.4', '<'):
            PHPWS_Core::initModClass('users', 'Permission.php');
            Users_Permission::registerPermissions('sdr', $content);
            $db = new PHPWS_DB;
            $result = $db->importFile(PHPWS_SOURCE_DIR . 'mod/sdr/boost/update/0.10.4.sql');
            if(PHPWS_Error::logIfError($result)) {
                return $result;
            }

        case version_compare($currentVersion, '0.11.5', '<'):
            PHPWS_Core::initModClass('users', 'Permission.php');
            Users_Permission::registerPermissions('sdr', $content);
    }

    return TRUE;
}
