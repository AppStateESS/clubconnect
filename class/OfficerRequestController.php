<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OfficerRequestController extends PDOController
{
    public function get($id = null, $username = null)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                r.officer_request_id,
                r.organization_id,
                r.member_id,
                CASE
                    WHEN r.member_id IS NOT NULL
                        THEN m.username
                    ELSE r.person_email
                END AS person_email,
                r.role_id,
                r.admin,
                r.submitted,
                r.approved,
                r.fulfilled
            FROM
                sdr_officer_request_view_current AS r
            LEFT OUTER JOIN sdr_member AS m
                ON r.member_id = m.id ".
            ($id ? "WHERE officer_request_id = :id" : "") .
            ($username ? "WHERE person_email = :username" : "") . "
            ORDER BY id");

        $params = array();
        if($id) {
            $params['id'] = $id;
        }
        if($username) {
            $params['username'] = $username;
        }

        if(!$this->safeExecute($stmt, $params)) return FALSE;

        $requests = array();
        $oldid = -1;
        $req = array();
        while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if($r['officer_request_id'] != $oldid) {
                if($oldid != -1) {
                    $requests[] = $req;
                }
                $oldid = $r['officer_request_id'];
                $req = array(
                    'officer_request_id' => $r['officer_request_id'],
                    'organization_id'    => $r['organization_id'],
                    'submitted'          => $r['submitted'],
                    'approved'           => $r['approved'],
                    'officers'           => array());
            }

            $req['officers'][] = array(
                'member_id'    => $r['member_id'],
                'person_email' => $r['person_email'],
                'role_id'      => $r['role_id'],
                'admin'        => $r['admin'],
                'fulfilled'    => $r['fulfilled']);
        }

        if($req) $requests[] = $req;    // Fencepost

        if(count($requests) == 0) return FALSE;

        return $requests;
    }

    public function create(array $offreq)
    {
        // Create Request Record
        $stmt = $this->pdo->prepare("
            INSERT INTO sdr_officer_request (
                officer_request_id, organization_id, submitted, approved, fulfilled
            ) VALUES (
                nextval('sdr_officer_request_seq'), :organization_id, now(), null, null
            ) RETURNING officer_request_id
        ");

        $result = $this->safeExecute($stmt, array(
            'organization_id' => $offreq['organization_id'],
        ));
        
        if(!$result) {
            return FALSE;
        }

        $row = $stmt->fetch(PDO::FETCH_NUM);

        $id = $row[0];

        // Insert Officers
        $stmt = $this->pdo->prepare("
            INSERT INTO sdr_officer_request_member (
                id, officer_request_id, person_email, role_id, admin, fulfilled
            ) VALUES (
                nextval('sdr_officer_request_member_seq'),
                :officer_request_id, :person_email, :role_id, :admin, null
            )
        ");

        foreach($offreq['officers'] AS $officer) {
            $result = $this->safeExecute($stmt, array(
                'officer_request_id' => $id,
                'person_email'       => $officer['person_email'],
                'role_id'            => $officer['role_id'],
                'admin'              => $officer['admin'] ? 1 : 0
            ));

            if(!$result) {
                return FALSE;
            }
        }

        return $id;
    }

    public function save(array $offreq)
    {
        if($offreq['approved']) {
            $stmt = $this->pdo->prepare('
                UPDATE sdr_officer_request SET approved = NOW()
                WHERE officer_request_id = :offreq
            ');

            if(!$this->safeExecute($stmt, array('offreq' => $offreq['officer_request_id']))) {
                return false;
            }
        }

        $stmt = $this->pdo->prepare('
            DELETE FROM sdr_officer_request_member
            WHERE officer_request_id = :offreq
        ');

        if(!$this->safeExecute($stmt, array('offreq' => $offreq['officer_request_id']))) {
            return false;
        }

        $member = $this->pdo->prepare("
            INSERT INTO sdr_officer_request_member (
                id, officer_request_id, member_id, person_email, role_id, admin, fulfilled
            ) VALUES (
                nextval('sdr_officer_request_member_seq'),
                :officer_request_id, :member_id, :person_email, :role_id, :admin, :fulfilled
            )
        ");

        if(!array_key_exists('member_id', $officer)) {
            $officer['member_id'] = null;
        }

        if(!array_key_exists('person_email', $officer)) {
            $officer['person_email'] = null;
        }

        foreach($offreq['officers'] as $officer) {
            $this->safeExecute($member, array(
                'officer_request_id' => $offreq['officer_request_id'],
                'member_id'          => $officer['member_id'],
                'person_email'       => $officer['person_email'],
                'role_id'            => $officer['role_id'],
                'admin'              => $officer['admin'],
                'fulfilled'          => $officer['fulfilled']
            ));
        }

        return $offreq['officer_request_id'];
    }

    protected function hasOfficer($officer, $set)
    {
        foreach($set as $o) {
            if($o['person_email'] == $officer['person_email'] ||
                $o['member_id'] == $officer['member_id']) return true;
        }

        return false;
    }

    public function fulfill($offreq_id, $username)
    {
        $offreq = $this->get($offreq_id);
        $offreq = $offreq[0];

        $req = null;
        foreach($offreq['officers'] as $r) {
            if($r['person_email'] == $username) {
                $req = $r;
                break;
            }
        }
        if(is_null($req)) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You are not a part of this officer request.');
        }

        if($req['fulfilled']) return;

        if(is_null($req['member_id'])) {
            // Try to Find Member
            PHPWS_Core::initModClass('sdr', 'Member.php');
            $member = new Member(null, $username);
            if(!is_null($member->getId())) {
                // Member Exists, link
                $req['member_id'] = $member->getId();

                $req['member_id'] = $member->getId();
            } else {
                // TODO: Try SOAP
                // Member does not exist, create
            
                $member = array(
                    'username'    => $username,
                    'prefix'      => null,
                    'suffix'      => null,
                    'first_name'  => $_SERVER['HTTP_SHIB_INETORGPERSON_GIVENNAME'],
                    'last_name'   => $_SERVER['HTTP_SHIB_PERSON_SURNAME'],
                    'middle_name' => null);
                $stmt = $this->pdo->prepare("
                    INSERT INTO sdr_member (
                        id,
                        username,
                        prefix,
                        suffix,
                        first_name,
                        middle_name,
                        last_name
                    ) VALUES (
                        nextval('sdr_member_seq'),
                        :username,
                        :prefix,
                        :suffix,
                        :first_name,
                        :middle_name,
                        :last_name
                    ) RETURNING id
                    ");

                $this->safeExecute($stmt, $member);
                $row = $stmt->fetchOne(PDO::FETCH_NUM);
                $req['member_id'] = $row[0];

                if($req['role_id'] == 53) {
                    $stmt = $this->pdo->prepare("
                        INSERT INTO sdr_advisor (
                            id
                        ) VALUES (
                            :id
                        )
                    ");
                    $advisor = array('id' => $req['member_id']);
                    $this->safeExecute($stmt, $advisor);
                } else {
                    throw new Exception('Not prepared to create Student record');
                }
            }

            $stmt = $this->pdo->prepare('
                UPDATE sdr_officer_request_member
                SET
                    member_id = :member_id,
                    fulfilled = now()
                WHERE
                        officer_request_id = :offreq_id
                    AND person_email = :username
            ');

            $vars = array(
                'member_id' => $member->getId(),
                'offreq_id' => $offreq_id,
                'username'  => $username
            );

            $this->safeExecute($stmt, $vars);
        } else {
            $stmt = $this->pdo->prepare('
                UPDATE sdr_officer_request_member
                SET fulfilled = now()
                WHERE
                        officer_request_id = :offreq_id
                        AND member_id = :member_id
            ');

            $vars = array(
                'member_id' => $req['member_id'],
                'offreq_id' => $offreq_id
            );

            $this->safeExecute($stmt, $vars);
        }
    }
}

?>
