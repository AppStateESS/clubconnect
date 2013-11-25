<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'command/ClubRegistrationFormCommand.php');

class ClubRegistrationPrintCommand extends ClubRegistrationFormCommand
{
    public function getRawFile()
    {
        return 'ClubRegistrationPrint.html';
    }

    public function get(CommandContext $context)
    {
        parent::get($context);

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
