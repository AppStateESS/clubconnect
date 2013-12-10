<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'PDOController.php');

class GPAController extends PDOController
{
    public function haveDataFor($term)
    {
        if(!Term::isValidTerm($term)) {
            return false;
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) AS cnt FROM sdr_special_gpa WHERE TERM=:term');
        $result = $this->safeExecute($stmt, array('term' => $term));

        $row = $stmt->fetch();
        return $row['cnt'] == 1;
    }
}

?>
