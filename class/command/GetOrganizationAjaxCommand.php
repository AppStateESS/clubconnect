<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class GetOrganizationAjaxCommand extends Command
{
    private $organization_id;
    private $term;

    public function getRequestVars()
    {
        $vars = array('action' => 'GetOrganizationAjax');

        if(isset($this->organization_id)) {
            $vars['organization_id'] = $this->organization_id;
        }

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        $this->organization_id = $context->get('organization_id');
        if(is_null($this->organization_id)) {
            throw new InvalidArgumentException('Please specify an Organization ID.');
        }

        $this->term = $context->get('term');

        PHPWS_Core::initModClass('sdr', 'Organization.php');
        $org = new Organization($this->organization_id, is_null($this->term) ? NULL : $this->term);

        if(!UserStatus::isAdmin()) {
            unset($org->banner_id);
            unset($org->rollover_stf);
            unset($org->rollover_fts);
            unset($org->student_managed);
            unset($org->instance_id);
            unset($org->bank);
            unset($org->ein);
        }

        $context->setContent($org);
    }
}

?>
