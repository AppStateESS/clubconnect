<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OrganizationRegistrationController extends PDOController
{
    public function get($id = null, $oid = null, $term = null, $orid = null)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                v.registration_id    AS v_registration_id,
                v.term               AS v_term,
                v.organization_id    AS v_organization_id,
                v.officer_request_id AS v_officer_request_id,
                v.updated            AS v_updated,
                v.updated_by         AS v_updated_by,
                v.state_updated      AS v_state_updated,
                v.state_updated_by   AS v_state_updated_by,
                v.parent             AS v_parent,
                v.fullname           AS v_fullname,
                v.shortname          AS v_shortname,
                v.address            AS v_address,
                v.bank               AS v_bank,
                v.ein                AS v_ein,
                v.purpose            AS v_purpose,
                v.description        AS v_description,
                v.requirements       AS v_requirements,
                v.meetings           AS v_meetings,
                v.location           AS v_location,
                v.website            AS v_website,
                v.elections          AS v_elections,
                v.searchtags         AS v_searchtags,
                v.sgaelection        AS v_sgaelection,
                v.state              AS v_state,
                v.statecomment       AS v_oldstatecomment,
                s.effective_date     AS s_effective_date,
                s.committed_by       AS s_committed_by,
                s.state              AS s_state
            FROM
                sdr_organization_registration_view_current AS v
            JOIN
                sdr_organization_registration_state AS s
                ON v.registration_id = s.registration_id
                ".
            ($id ? "WHERE v.registration_id = :id" : "") .
            ($oid && $term ? "WHERE v.organization_id = :oid AND v.term = :term" : "") .
            ($orid ? "WHERE v.officer_request_id = :orid" : "")."
            ORDER BY v.registration_id, s.effective_date
        ");

        $params = array();
        if($id) {
            $params['id'] = $id;
        }
        if($oid && $term) {
            $params['oid'] = $oid;
            $params['term'] = $term;
        }
        if($orid) {
            $params['orid'] = $orid;
        }

        if(!$this->safeExecute($stmt, $params)) return FALSE;

        $cmd = CommandFactory::getInstance()->get('ClubRegistrationFormCommand');

        $regs = array();
        $oldid = -1;
        while($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if($r['v_registration_id'] != $oldid) {
                if($oldid != -1) {
                    $regs[] = $reg;
                }
                $oldid = $r['v_registration_id'];
                $reg = $this->unPrefix('v_', $r);

                // Split up search tags
                $reg['searchtags'] = explode(', ', $reg['searchtags']);

                // Split up the election months
                $reg['elections'] = explode(', ', $reg['elections']);

                $cmd->setRegistrationId($reg['registration_id']);
                $reg['url'] = $cmd->getURI();

                $reg['history'] = array();
            }

            $reg['history'][] = $this->unPrefix('s_', $r);
        }

        if($oldid != -1) {
            $regs[] = $reg; // fencepost
        }

        if(count($regs) == 0) return FALSE;

        return $regs;
            
    }

    public function create(array $reg)
    {
        // Create Registration
        $stmt = $this->pdo->prepare("
            INSERT INTO sdr_organization_registration (
                registration_id, term, organization_id, officer_request_id
            ) VALUES (
                nextval('sdr_organization_registration_seq'), :term,
                :organization_id, :officer_request_id
            ) RETURNING registration_id
        ");

        if(!array_key_exists('term', $reg) || !$reg['term']) {
            $reg['term'] = Term::getCurrentTerm();
        } else {
            if(!UserStatus::hasPermission('registration_admin') &&
                    $req['term'] != Term::getCurrentTerm()) {
                throw new PermissionException('Cannot set term if not an admin');
            }
        }

        $result = $this->safeExecute($stmt, array(
            'term'               => $reg['term'],
            'organization_id'    => $reg['organization_id'],
            'officer_request_id' => $reg['officer_request_id']));

        if(!$result) {
            return FALSE;
        }

        $row = $stmt->fetch(PDO::FETCH_NUM);

        $reg['registration_id'] = $row[0];

        // Create Data Record
        $result = $this->saveData($reg);

        if(!$result) {
            return FALSE;
        }

        // Create State Record
        $result = $this->saveState($reg);

        if(!$result) {
            return FALSE;
        }

        return $reg['registration_id'];
    }
            
    public function save(array $reg)
    {
        $old = $this->get($reg['registration_id']);
        $old = $old[0];

        if($this->assocDiff($old, $reg, array(
            'fullname', 'shortname', 'address', 'bank', 'ein', 'purpose', 'description',
            'requirements', 'meetings', 'location', 'website', 'elections', 'searchtags',
            'sgaelection'))) {
            $this->saveData($reg);
        }

        if($this->assocDiff($old, $reg, array('state'))) {
            $this->saveState($reg);
        }

        return $reg['registration_id'];
    }

    public function saveData(array $reg)
    {
        $stmt = $this->pdo->prepare('
            UPDATE
                sdr_organization_registration_data
            SET
                effective_until = NOW()
            WHERE
                    registration_id = :regid
                AND effective_until IS NULL
        ');

        if(!$this->safeExecute($stmt, array('regid' => $reg['registration_id']))) {
            return false;
        }

        $stmt = $this->pdo->prepare('
            INSERT INTO sdr_organization_registration_data (
                registration_id, effective_date, effective_until, committed_by, parent,
                fullname, shortname, address, bank, ein, purpose, description, requirements,
                meetings, location, website, elections, searchtags, sgaelection
            ) VALUES (
                :registration_id, NOW(), NULL, :committed_by, :parent, :fullname, :shortname,
                :address, :bank, :ein, :purpose, :description, :requirements, :meetings, :location,
                :website, :elections, :searchtags, :sgaelection
            )
        ');

        return $this->safeExecute($stmt, array(
            'registration_id' => $reg['registration_id'],
            'committed_by'    => $reg['committed_by'],
            'parent'          => $reg['parent'],
            'fullname'        => $reg['fullname'],
            'shortname'       => $reg['shortname'],
            'address'         => $reg['address'],
            'bank'            => $reg['bank'],
            'ein'             => $reg['ein'],
            'purpose'         => $reg['purpose'],
            'description'     => $reg['description'],
            'requirements'    => $reg['requirements'],
            'meetings'        => $reg['meetings'],
            'location'        => $reg['location'],
            'website'         => $reg['website'],
            'elections'       => implode(', ', $reg['elections']),
            'searchtags'      => implode(', ', $reg['searchtags']),
            'sgaelection'     => $reg['sgaelection']));
    }

    public function saveState(array $reg)
    {
        $oldreg = $this->get($reg['registration_id']);
        if($oldreg != FALSE) {
            $oldreg = $oldreg[0];
            $oldstate = $oldreg['state'];

            if($oldstate == $reg['state']) {
                // No Transition
                return false;
            }
        } else {
            $oldstate = 'New';
        }

        $stmt = $this->pdo->prepare('
            UPDATE
                sdr_organization_registration_state
            SET
                effective_until = NOW()
            WHERE
                    registration_id = :regid
                AND effective_until IS NULL
        ');

        if(!$this->safeExecute($stmt, array('regid' => $reg['registration_id']))) {
            return false;
        }

        $stmt = $this->pdo->prepare('
            INSERT INTO sdr_organization_registration_state (
                registration_id, effective_date, effective_until, committed_by, state, comment
            ) VALUES (
                :registration_id, NOW(), NULL, :committed_by, :state, :comment
            )
        ');

        $vals = array(
            'registration_id' => $reg['registration_id'],
            'committed_by'    => $reg['committed_by'],
            'state'           => $reg['state'],
            'comment'         => null,
        );

        if(array_key_exists('statecomment', $reg) && $reg['statecomment']) {
            $vals['comment'] = $reg['statecomment'];
        }

        $this->safeExecute($stmt, $vals);

        $this->doTransition($reg, $oldstate, $reg['state']);

        return true;
    }

    public function doTransition($reg, $old, $new)
    {
        if($old == 'Approved' && $new == 'Certified') {
            PHPWS_Core::initModClass('sdr', 'process/RegistrationCertified.php');
            $process = new RegistrationCertified();
            $process->execute($reg);
        }
    }

    public function countSubmitted()
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM sdr_organization_registration_view_current WHERE state=:state');

        if(!$this->safeExecute($stmt, array('state' => 'Submitted'))) return false;

        $result = $stmt->fetch(PDO::FETCH_NUM);
        return $result[0];
    }

    public function addPermissions(&$reg, $user)
    {
        $reg['allowView']   = $this->allowView($reg, $user);
        $reg['allowModify'] = $this->allowModify($reg, $user);
        $reg['allowState']  = $this->allowState($reg, $user);
    }

    public function protect(&$reg, $user)
    {
        if(!$this->allowView($reg, $user)) {
            $reg['bank'] = empty($reg['bank']) ? 'Set' : 'Not Set';
            $reg['ein']  = empty($reg['ein']) ? 'Set' : 'Not Set';
        }
    }

    public function allowView($reg, $user)
    {
        if(UserStatus::hasPermission('registration_admin')) return true;
    }

    public function allowModify($reg, $user)
    {
        return UserStatus::hasPermission('registration_admin');
    }

    public function allowState($reg, $user)
    {
        return false;
    }

    public function countPending()
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) AS ct
            FROM
                sdr_organization_registration_view_current
            WHERE
                state = 'Submitted'
        ");

        $this->safeExecute($stmt, array());

        $row = $stmt->fetch(PDO::FETCH_NUM);
        return $row[0];
    }
}

?>
