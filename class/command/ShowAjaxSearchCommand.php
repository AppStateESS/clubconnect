<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowAjaxSearchCommand extends Command
{
    public $type;

    public function getRequestVars()
    {
        $vars =  array('action' => 'ShowAjaxSearch');

        if(isset($this->type)) {
            $vars['type'] = $this->type;
        }

        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(UserStatus::isGuest()) {
            PHPWS_Core::initModClass('exception/PermissionException.php');
            throw new PermissionException('You do not have permission to search for students.');
        }

        PHPWS_Core::initModClass('sdr', 'StudentSearchAjaxView.php');

        $search = new StudentSearchAjaxView();

        $context->setContent($search->show());
    }
}

?>
