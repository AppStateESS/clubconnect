<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class OrganizationSearchCommand extends Command
{

    protected $type;
    protected $searchFor;

    public function setType($type){
        $this->type = $type;
    }
    
    public function setSearchFor($searchFor){
        $this->searchFor = $searchFor;
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'OrganizationSearch.php');
        $search = new OrganizationSearch();

        $search->setSearchField($context->get('searchFor'));
        $search->setSearchType($context->get('type'));

        $search->doSearch();
        $context->setContent($search->getResult());
    }

}

?>
