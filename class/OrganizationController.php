<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OrganizationController extends PDOController
{
    public function create(array $org)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO sdr_organization (
                id, banner_id, locked, reason_access_denied, rollover_stf,
                rollover_fts, student_managed
            ) VALUES (
                nextval('sdr_organization_seq'), :banner_id, :locked, :reason,
                :rollover_stf, :rollover_fts, :student_managed
            ) RETURNING id
        ");

        $result = $this->safeExecute($stmt, $org);

        if(!$result) return FALSE;

        $row = $stmt->fetch(PDO::FETCH_NUM);

        return $row[0];
    }

    public function getRegistrableOrganizations($member_id)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                o.id,
                o.name,
                r.registration_id
            FROM
                sdr_organization_recent AS o
            JOIN sdr_membership AS m
                 ON o.id   = m.organization_id
                AND o.term = m.term
            LEFT OUTER JOIN
                (SELECT * FROM sdr_organization_registration WHERE term IN (201240,201310)) AS r
                 ON o.id   = r.organization_id
            LEFT OUTER JOIN 
                (SELECT * FROM sdr_organization_registration WHERE term=201340) AS rc
                 ON o.id   = rc.organization_id
            WHERE 
                    m.term IN (201240, 201310)
                AND m.member_id = :member_id
                AND rc.registration_id IS NULL
        ");

        $params = array(
            'member_id' => $member_id
        );

        $result = $this->safeExecute($stmt, $params);

        if(!$result) return FALSE;

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

?>
