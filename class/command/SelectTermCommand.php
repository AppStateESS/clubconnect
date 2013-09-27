<?php

/**
 * Marks a term as 'selected' in the user's session.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class SelectTermCommand extends Command {

    protected $term;

    public function setTerm($term) {
        $this->term = $term;
    }

    function execute(CommandContext $context)
    {
        if(UserStatus::isGuest()) {
            $context->goBack();
        }

        if(!isset($this->term)) {
            $this->term = $context->get('term');
        }

        Term::setSelectedTerm($this->term);

        $context->goBack();
    }
}

?>
