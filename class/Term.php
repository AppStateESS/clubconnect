<?php

/**
 * SDR Term
 * Maintains the "current" term, "active" term, and handles tasks related
 * to creating new terms.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

define('TERM_SPRING',   10);
define('TERM_SUMMER1',  20);
define('TERM_SUMMER2',  30);
define('TERM_FALL',     40);

define('SPRING', 'Spring');
define('SUMMER1', 'Summer 1');
define('SUMMER2', 'Summer 2');
define('FALL', 'Fall');

class Term
{
	public $term;
	public $sdr_version;
    public $ignore; // Ignored terms can never be selected or marked current.
	
    public static function getCurrentTerm()
    {
    	return SDRSettings::getCurrentTerm();
    }

    public static function setCurrentTerm($term)
    {
    	SDRSettings::setCurrentTerm($term);
    }

    public static function getPrintableCurrentTerm()
    {
        return self::toString(self::getCurrentTerm());
    }

    public static function getSelectedTerm()
    {
        if(isset($_SESSION['selected_term'])) {
            return $_SESSION['selected_term'];
        } else {
            return self::getCurrentTerm();
        }
    }

    public static function setSelectedTerm($term)
    {
        $_SESSION['selected_term'] = $term;
        return;
    }

    public static function getPrintableSelectedTerm()
    {
        return self::toString(self::getSelectedTerm());
    }

    public static function isCurrentTermSelected()
    {
        return self::getSelectedTerm() == self::getCurrentTerm();
    }

    public static function getTermYear($term)
    {
        return substr($term, 0, 4);
    }

    public static function getTermSem($term)
    {
        return substr($term, 4, 2);
    }

    public static function getTermOldSem($term)
    {
        return substr($term, 4, 1);
    }
    
    public static function toString($term, $concat = TRUE)
    {
        # Grab the year from the entry_term
    	$result['year'] = Term::getTermYear($term);

        # Grab the term from the entry_term
        $sem = Term::getTermSem($term);
    
        if($sem == TERM_SPRING){
            $result['term'] = SPRING;
        }else if($sem == TERM_SUMMER1){
            $result['term'] = SUMMER1;
        }else if($sem == TERM_SUMMER2){
            $result['term'] = SUMMER2;
        }else if($sem == TERM_FALL){
            $result['term'] = FALL;
        }else{
            PHPWS_Core::initModClass('sdr','exception/TermException.php');
            throw new TermException("Bad term: $term");
        }

        if($concat){
            return $result['year'] . ' ' . $result['term'];
        }else{
            return $result;
        }
    }
    
    /*************************
     * Static helper methods *
     *************************/
    
    /**
     * Returns a list of all the terms currently available. Useful for making drop down boxes.
     * @return Array Associate array of terms and their textual representations.
     */
    public static function getTerms($onlySelectable = FALSE)
    {
        $db = new PHPWS_DB('sdr_term');
        if($onlySelectable) {
            $db->addWhere('selectable', 1);
        }
        $db->addOrder('term desc');
        $result = $db->getObjects('Term');

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        
        return $result;
    }

    /**
     * Checks a term to see if it really exists in the database.
     * @return boolean True if it exists, False if it doesn't
     */
    public static function isValidTerm($term)
    {
        $db = new PHPWS_DB('sdr_term');
        $result = $db->select('col');

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
        
        return in_array($term, $result);
    }

    public static function validateTerm($term)
    {
        if(!self::isValidTerm($term)) {
            PHPWS_Core::initModClass('sdr', 'exception/InvalidTermException.php');
            throw new InvalidTermException("$term is not a valid term.");
        }
    }
    
    public static function getTermsAssoc($onlySelectable = FALSE)
    {
    	$objs = self::getTerms($onlySelectable);
    	
    	$terms = array();
    	
    	foreach($objs as $term) {
    		$t = $term->term;
    		$terms[$t] = Term::toString($t);
    	}
    	
    	return $terms;
    }

    public static function getTermSelector()
    {
        if(UserStatus::isGuest()) {
            return dgettext('sdr', 'ClubConnect');
        }

        $terms = self::getTermsAssoc(TRUE);

        $current = self::getCurrentTerm();
        $terms[$current] .= ' (Current)';

        $form = new PHPWS_Form('term_selector');
        
        $cmd = CommandFactory::getCommand('SelectTerm');
        $cmd->initForm($form);

        $form->addDropBox('term', $terms);
        $form->setMatch('term', self::getSelectedTerm());

        $tags = $form->getTemplate();
        javascript('modules/sdr/SelectTerm');
        return PHPWS_Template::process($tags, 'sdr', 'SelectTerm.tpl');
    }
}

?>
