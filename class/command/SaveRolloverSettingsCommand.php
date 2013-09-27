<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class SaveRolloverSettingsCommand extends Command
{
    private $rollover_column;

    function setRolloverColumn($col)
    {
        $this->rollover_column = $col;
    }

    function getRequestVars()
    {
        $vars = array('action' => 'SaveRolloverSettings');

        if(isset($this->rollover_column)) {
            $vars['rollover_column'] = $this->rollover_column;
        }

        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('Permission denied.');
        }

        $rollover_column = $context->get('rollover_column');
        if(!($rollover_column == 'rollover_stf' || $rollover_column == 'rollover_fts')) {
            throw new InvalidArgumentException("rollover_column did not make sense ($rollover_column)");
        }

        $db = new PHPWS_DB('sdr_organization');
        $db->addValue($rollover_column, FALSE);
        $db->update();

        foreach($_REQUEST['rollover'] as $id=>$val) {
            if($val != 'on') continue;
            $db = new PHPWS_DB('sdr_organization');
            $db->addWhere('id', $id);
            $db->addValue($rollover_column, TRUE);
            $db->update();
        }

        $cmd = CommandFactory::getCommand('ShowRollover');
        $cmd->redirect();
    }
}

?>
