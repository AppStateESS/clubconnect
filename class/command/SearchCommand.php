<?php

PHPWS_Core::initModClass('sdr', 'Command.php');

class SearchCommand extends Command
{

    protected $type;
    protected $searchFor;

    public function setType($type){
        $this->type = $type;
    }

    /*public function getParams()
    {
        return array('type', 'searchFor');
    }*/

    public function execute(CommandContext $context)
    {
        if(UserStatus::isGuest()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to search for students or advisors.');
        }
        
        PHPWS_Core::initModClass('sdr', 'StudentSearch.php');

        $search = new StudentSearch();

        $search->setSearchField($context->get('searchFor'));
        $search->setSearchType($context->get('type'));

        $search->doSearch();
        $result = $search->getDb()->select();

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException('Could not load search results.');
        }

        $context->setContent($result);

    }

}

?>
