<?php

/**
 * Shows the Organization Profile Edit View
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

define('MAX_CLUB_LOGO_WIDTH', 300);
define('MAX_CLUB_LOGO_HEIGHT', 300);

define('CLUB_LOGO_URI', 'images/club_logos/');
define('CLUB_LOGO_PATH', PHPWS_HOME_DIR . CLUB_LOGO_URI); 

class EditOrganizationProfileCommand extends CrudCommand
{
	protected $organization_id;
    
    public function setOrganizationId($id)
    {
        $this->organization_id = $id;
    }

    public function getParams()
    {
        return array('organization_id');
    }
	
    public function get(CommandContext $context)
    {
        // If Global Lock is enabled then user can't edit the profile
        PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
        if(GlobalLock::isLocked() && !UserStatus::isAdmin()){
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException(
				      dgettext('sdr', GlobalLock::persistentMessage()));
        }
        if(!isset($this->organization_id)) {
	        $this->organization_id = $context->get('organization_id');
        }
        $orgid = $this->organization_id;
        
        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        $orgmanager = new OrganizationManager($orgid);
        
        $context->setContent($orgmanager->editProfile());
    }

    public function post(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationProfile.php');

        $profile = OrganizationProfile::getByOrganizationId($this->organization_id);

        $result = $context->plugObject($profile);

        if($result !== TRUE) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('plugObject Failed');
        }

        // Check the club URL
        $url = $profile->site_url;
        if(!is_null($profile->site_url) || !empty($profile->site_url)){
            //Make sure the url begins with 'http://'
            if(preg_match('@^(https?://)@i',$profile->site_url) == 0){
                $profile->site_url = 'http://' . $profile->site_url;
            }

            // Make sure it's valid input
            if(!PHPWS_Text::isValidInput($profile->site_url, 'url')){
                $profile->site_url = "";
            }
        }

        // If the user wants to upload a new club logo
        if (!empty($_FILES['logo_file']['name'])) {
            $errors = array();

            PHPWS_Core::initModClass('filecabinet', 'Image.php');
            $image = new PHPWS_Image;
            $image->setDirectory(CLUB_LOGO_PATH);
            $image->setMaxWidth(MAX_CLUB_LOGO_WIDTH);
            $image->setMaxHeight(MAX_CLUB_LOGO_HEIGHT);
            // the filename will be the organization's id

            if (!$image->importPost('logo_file', false, true)) {
                if (isset($image->_errors)) {
                    foreach ($image->_errors as $oError) {
                        $errors[] = $oError->getMessage();
                    }
                }
            } elseif ($image->file_name) {

                // Check for and delete an existing file of the same name
                if(is_file(CLUB_LOGO_PATH . $profile->getOrganizationId() .'.'. $image->getExtension())){
                    unlink(CLUB_LOGO_PATH . $profile->getOrganizationId() .'.'. $image->getExtension());
                }

                $image->setFilename($profile->getOrganizationId() .'.'. $image->getExtension());

                $result = $image->write();
                if (PHPWS_Error::logIfError($result)) {
                    $errors[] = array(dgettext('sdr', 'There was a problem saving your club logo image.'));
                } else {
                    $profile->setClubLogo(CLUB_LOGO_URI . $profile->getOrganizationId() . '.' . $image->getExtension());
                }
            }

            if(sizeof($errors) > 0){
                //NQ::simple('sdr', 1, 'There was a problem saving the club logo image.');
                $cmd = CommandFactory::getCommand('ShowOrganizationProfile');
                $cmd->setOrganizationId($profile->getOrganizationId());
                $cmd->redirect();
            }
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationProfileController.php');
        $controller = new OrganizationProfileController($profile);
        $result = $controller->save();

        if($result) {
            PHPWS_Core::initModClass('notification', 'NQ.php');
            NQ::simple('sdr', 1, 'Profile Updated.');
             
            $cmd = CommandFactory::getCommand('ShowOrganizationProfileCommand');
            $cmd->setOrganizationId($profile->getOrganizationId());
            $cmd->redirect();
        } else {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('Failed to save the profile object.');
        }
    }
}

?>
