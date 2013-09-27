<?php
/*
 * Organization Save Handler
 *
 * Handles processing input and saving/updating an Organization Profile
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package mod
 * @subpackage sdr
 */

PHPWS_Core::initModClass('sdr', 'SaveHandler.php');
PHPWS_Core::initModClass('sdr', 'OrganizationProfile.php');

class SDR_OrganizationProfileSaveHandler extends SDR_SaveHandler
{
    public function __construct($request)
    {
        $this->table = 'sdr_organization_profile';
        $this->class = 'OrganizationProfile';
        $this->request = $request;
    }

    public function saveObject()
    {
        $profile = new OrganizationProfile($this->request['organization_id'], null, true);
        if(PHPWS_Core::plugObject($profile, $this->request)){
            $db = new PHPWS_DB($this->table);
            $result = $db->saveObject($profile);

            if(PHPWS_Error::logIfError($result)){
                return false;
            }

            return true;
        }
        return false;
    }
}
?>
