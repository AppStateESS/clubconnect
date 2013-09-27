<?php

class SpecialGPA
{
    var $id;
    var $term;

    var $enrolled_cumulative_female;
    var $enrolled_cumulative_male;
    var $enrolled_cumulative_overall;
    var $enrolled_previous_overall;
    var $enrolled_previous_male;
    var $enrolled_previous_female;

    public function __construct($id = NULL, $term = NULL)
    {
        if(is_null($term) && is_null($id)) {
            return;
        }

        $this->setId($id);
        $this->setTerm($term);
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('sdr_special_gpa');
        if(!is_null($this->id)) {
            $db->addWhere('id', $this->id);
        } else if(!is_null($this->term)) {
            $db->addWhere('term', $this->term);
        } else {
            throw new InvalidArgumentException('Neither id nor term was set for SpecialGPA');
        }

        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }
}

?>
