<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Loader.php');

class CountStudentsByTypeByTermLoader extends Loader
{
    protected $term;
    protected $type;

    public function __construct($type, $term)
    {
        $this->db = new PHPWS_DB('sdr_student');
        $this->db->addJoin('left', 'sdr_student', 'sdr_student_registration', 'id', 'student_id');

        $this->setTerm($term);
        $this->setType($type);
    }

    public function setTerm($term)
    {
        Term::validateTerm($term);
        $this->term = $term;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function load()
    {
        $this->db->addWhere('sdr_student.transfer', $this->type);
        $this->db->addWhere('sdr_student_registration.term', $this->term);
        $result = $this->db->count();

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'DatabaseException.php');
            throw new DatabaseException('Could not load non-transfers by term.');
        }

        return $result;
    }
}

?>
