<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');
PHPWS_Core::initModClass('sdr', 'PDOFactory.php');

class PeopleSearchCommand extends CrudCommand
{
    public function allowExecute()
    {
        return UserStatus::isAdmin();
    }

    public function get(CommandContext $context)
    {
        if($context->has('search')) {
            $this->doFuzzySearch($context);
        } else
        if($context->has('username')) {
            $this->doUsernameSearch($context);
        }
    }

    protected function linkToProfile(&$arr)
    {
        $profile = CommandFactory::getInstance()->get('ShowMemberInfoCommand');

        foreach($arr as &$row) {
            $profile->setMemberId($row['id']);
            $row['url'] = array(
                'default' => $profile->getURI()
            );
        }
    }

    protected function doFuzzySearch(CommandContext $context)
    {
        $search = $context->get('search');

        // No results if no search string provided or if less than three 
        // characters provided becuase there is no way I can ajax the whole list 
        // of people.
        if(!$search || strlen($search) < 3) {
            $context->setContent(array());
            return;
        }

        // Remove things that are not letters or spaces
        $search = preg_replace('/[^\w ]/', '', $search);

        // Add percent signs
        $search = preg_replace('/\b/', '%', $search);

        // Split up the search terms so that order does not matter
        $terms = explode(' ', $search);

        PHPWS_Core::initModClass('sdr', 'PersonController.php');

        $ctrl = new PersonController();
        $result = $ctrl->searchFuzzy($terms);

        $this->linkToProfile($result);

        $context->setContent($result);
    }

    protected function doUsernameSearch(CommandContext $context)
    {
        $username = $context->get('username');

        PHPWS_Core::initModClass('sdr', 'PersonController.php');

        $ctrl = new PersonController();
        $result = $ctrl->searchUsername($username);

        $this->linkToProfile($result);

        $context->setContent($result);
    }
}

?>
