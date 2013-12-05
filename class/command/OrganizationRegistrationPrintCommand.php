<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class OrganizationRegistrationPrintCommand extends CrudCommand
{
    protected $registration_id;

    public function allowExecute()
    {
        return !UserStatus::isGuest();
    }

    public function getParams()
    {
        return array('registration_id');
    }

    public function get(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationController.php');
        $regCtrl = new OrganizationRegistrationController();

        PHPWS_Core::initModClass('sdr', 'OfficerRequestController.php');
        $orCtrl = new OfficerRequestController();

        $reg = $regCtrl->get($this->registration_id);
        if(empty($reg)) {
            throw new Exception('Could not load registration with ID ' . $this->registration_id);
            return;
        }
        $reg = $reg[0];

        $or = $orCtrl->get($reg['officer_request_id']);
        if(!$or) {
            throw new Exception('Could not load officer request with ID ' .
                $reg['officer_request_id'] . ' referenced from registration with ID ' .
                $reg['id']);
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationPrintSettings.php');
        PHPWS_Core::initModClass('sdr', 'OrganizationRegistrationPDF.php');
        $settings = new OrganizationRegistrationPrintSettings(UserStatus::getUsername());
        $pdf = new OrganizationRegistrationPDF($reg, $or, $settings);

        $filename = '/tmp/derp.pdf';
        $pdf->Output($filename, 'F');

        if(!file_exists($filename)) {
            PHPWS_Core::initModClass('sdr', 'exception/PDFGeneratorException.php');
            throw new PDFGeneratorException('An error occurred generating a printable Club Registration Form.');
        }

        $downloadFilename = 'club-registration-'.$reg['term'].'-'.$reg['organization_id'].'.pdf';

        header('Content-type: application/pdf');
        header('Content-Transfer-Encoding: Binary');
        header('Content-Disposition: attachment; filename="'.$downloadFilename.'"');
        readfile($filename);
        unlink($filename);
        exit();

        $template = array(
            'CONTENT' => $context->getContent(),
            'THEME_HTTP'=> Layout::getThemeHttpRoot() . Layout::getCurrentTheme() . '/'
        );

        $file = 'themes/' . Layout::getCurrentTheme() . '/blank.tpl';

        $jsHead = array();
        if(isset($GLOBALS['Layout_JS'])) {
            foreach($GLOBALS['Layout_JS'] as $script=>$javascript) {
                $jsHead[] = $javascript['head'];
            }
        }

        $template['JAVASCRIPT'] = implode("\n", $jsHead);

        echo PHPWS_Template::process($template, 'layout', $file, true);
        exit();
    }
}

?>
