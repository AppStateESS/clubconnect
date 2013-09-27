<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class SearchOrganizationscommand extends Command
{
    private $searchfor;

    function getRequestVars()
    {
        $vars = array('action' => 'Search');

        if(!is_null($this->searchfor)) {
            $vars['searchfor'] = $this->searchfor;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationSearch.php');
        $search = new OrganizationSearch();

        $search->setSearchField($context->get('searchfor'));

        $search->doSearch();
        $context->setContent($search->getResult());
    }
}

?>
