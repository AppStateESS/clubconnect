<?php
/*
 * SDR Organization Profile Edit
 *
 * Creates the edit screen for the Organization Profile.
 *
 * @author Daniel West <dwest at tux dot appstate dot edu>
 * @package mod
 * @subpackage sdr
 */

PHPWS_Core::initModClass('filecabinet', 'Cabinet.php');

class OrganizationProfileEdit extends sdr\View
{
    var $profile;

    public function __construct(OrganizationProfile $profile)
    {
        $this->profile = $profile;
    }

    public function show()
    {
        $tpl = array();

        //Setup for file_manager
        $manager = Cabinet::fileManager('club_logo', 0, 'sdr');
        $manager->setMaxWidth(300);
        $manager->setMaxHeight(300);
        $manager->forceResize();
        $manager->imageOnly();
        
        $cmd = CommandFactory::getCommand('EditOrganizationProfile');
        $cmd->setOrganizationId($this->profile->organization_id);

        $form = new PHPWS_Form('edit_organization_profile');
        
        $cmd->initForm($form);
        $form->addHidden('id', $this->profile->id);
        
        $form->addTextArea('purpose', $this->profile->purpose);
        
        $form->addFile('logo_file');
        
        $form->addTextArea('meeting_date', $this->profile->meeting_date);
        $form->setRows('meeting_date',3);
        
        $form->addTextArea('meeting_location', $this->profile->meeting_location);
        $form->setRows('meeting_location',3);
        
        $form->addTextArea('description', $this->profile->description);
        $form->useEditor('description');
        
        $form->addText('site_url', $this->profile->site_url);
        
        $form->addSubmit('submit', 'Update');

        $tpl = $form->getTemplate();
        $tpl['CLUB_LOGO'] = '<img src="' . $this->profile->getClubLogo() . '">';

        return PHPWS_Template::process($tpl, 'sdr', 'OrganizationProfileEdit.tpl');
    }
}

?>
