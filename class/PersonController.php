<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PersonController extends PDOController
{
    public function getAll()
    {
        throw new Exception('What, get every PERSON in SDR?  Haha, no.  I like the Networking people better than that.');
    }

    public function searchFuzzy($terms)
    {
        // Have to build up the prepared statement dynamically based on count of 
        // search terms.  This loses some of the benefits of prepared 
        // statements, but we're not evaluating it repeatedly, and it still has 
        // all the wonderful injection protection.
        $pg_hack = substr(str_repeat(
            'm.first_name || m.middle_name || m.last_name || m.username || to_char(m.id, \'999999999\') ILIKE ? AND ',
            count($terms)), 0, -5);

        $pdo = $this->pdo;

        $stmt = $pdo->prepare("
            SELECT
                m.id,
                m.username,
                m.first_name AS firstname,
                m.middle_name AS middlename,
                m.last_name AS lastname,
                m.last_name || ', ' || m.first_name || ' ' || m.middle_name AS fullname,
                s.id IS NOT NULL AS student,
                a.id IS NOT NULL AS facstaff
            FROM
                sdr_member AS m
            LEFT OUTER JOIN
                sdr_student AS s
                ON m.id = s.id
            LEFT OUTER JOIN
                sdr_advisor AS a
                ON m.id = a.id
            WHERE $pg_hack LIMIT 250");

        $this->safeExecute($stmt, $terms);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchUsername($username)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                m.id,
                m.username,
                m.first_name AS firstname,
                m.middle_name AS middlename,
                m.last_name AS lastname,
                m.last_name || ', ' || m.first_name || ' ' || m.middle_name AS fullname,
                s.id IS NOT NULL AS student,
                a.id IS NOT NULL AS facstaff,
                levenshtein(?, m.username) AS lev
            FROM
                sdr_member AS m
            LEFT OUTER JOIN
                sdr_student AS s
                ON m.id = s.id
            LEFT OUTER JOIN
                sdr_advisor AS a
                ON m.id = a.id
            ORDER BY lev LIMIT 250");

        $this->safeExecute($stmt, array($username));

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
