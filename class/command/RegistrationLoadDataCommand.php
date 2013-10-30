<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');
PHPWS_Core::initModClass('sdr', 'PDOFactory.php');

class RegistrationLoadDataCommand extends CrudCommand
{
    public function get(CommandContext $context)
    {
        $search = $context->get('search');
        if(!$search) {
            exit();
        }
        $pdo = PDOFactory::getInstance();

        $stmt = $pdo->prepare("
            SELECT
                o.id,
                o.name AS fullname,
                o.address,
                o.bank,
                o.ein,
                p.purpose,
                p.description,
                p.meeting_date AS meetings,
                p.meeting_location AS location,
                p.site_url AS website,
                a.elections
            FROM
                sdr_organization_recent AS o
            LEFT OUTER JOIN
                sdr_organization_profile AS p
                ON o.id = p.organization_id
            LEFT OUTER JOIN
                sdr_organization_application AS a
                ON o.id = a.organization_id
            WHERE
                o.term in (201140, 201210, 201240, 201310)
            AND o.name ilike :name
            AND (
                    a.term is null
                 OR a.term in (201140, 201210, 201240, 201310)
                )
            ORDER BY a.term DESC
            LIMIT 1
        ");

        if(!$stmt->execute(array('name' => "%$search%"))) {
            $e = new SdrPdoException('An error occurred on the server. Please try again later.');
            $e->setErrorInfo($stmt->errorInfo());
            throw $e;
        }

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if(count($result) < 1) {
            $context->setContent(array('error' => 'No matching club was registered last year.'));
            return;
        }

        $response = $result[0];

        $response['elections'] = explode(', ', $response['elections']);
        $response['ein'] = !is_null($response['ein']);

        // Try to get the people too
        $stmt = $pdo->prepare("
            SELECT
                m.username AS email,
                ms.administrator AS admin,
                mr.role_id AS position
            FROM
                sdr_member AS m
            JOIN
                sdr_membership AS ms
                ON ms.member_id = m.id
            JOIN
                sdr_membership_role AS mr
                ON ms.id = mr.membership_id
            JOIN
                sdr_role AS r
                ON mr.role_id = r.id
            WHERE
                ms.organization_id = :id
            AND ms.term = 201310
            AND r.rank > 0
            AND r.hidden = 0
        ");

        if(!$stmt->execute(array('id' => $response['id']))) {
            $e = new SdrPdoException('An error occurred on the server. Please try again later.');
            $e->setErrorInfo($stmt->errorInfo());
            throw $e;
        }

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['officers'] = $result;

        $context->setContent($response);
    }
}

?>
