<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class PrepareAngularViewCommand extends Command
{
    protected $href;

    public function getParams()
    {
        return array('href');
    }

    public function execute(CommandContext $context)
    {
        $href = preg_replace('/\.\./', '', $this->href);

        echo file_get_contents(PHPWS_SOURCE_DIR . 'mod/sdr/templates/Angular/' . $href);
    }
}

?>
