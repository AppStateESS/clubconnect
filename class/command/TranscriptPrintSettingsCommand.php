<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'TranscriptPrintSettings.php');
PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class TranscriptPrintSettingsCommand extends CrudCommand
{
    protected $ctrl;

    public function __construct()
    {
        $this->ctrl = new TranscriptPrintSettings(UserStatus::getUsername());
    }

    public function get(CommandContext $context)
    {
        $stg = $this->ctrl->get();
        $stg['post_uri'] = $this->getURI();
        $stg['name'] = $_SERVER['HTTP_SHIB_INETORGPERSON_DISPLAYNAME'];
        $stg['revision'] = 'Revision ' . $stg['setseq'];
        if($stg['username'] == 'default') {
            $stg['revision'] = 'From Defaults';
        }

        $context->setContent(
            PHPWS_Template::process($stg, 'sdr', 'TranscriptPrintSettings.tpl'));
    }

    public function post(CommandContext $context)
    {
        $this->ctrl->save($context->getParams());

        NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS, 'Settings updated.');

        $this->redirect();
    }
}

?>
