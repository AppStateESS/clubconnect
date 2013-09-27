<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class Rollover
{
    private $from_term;
    private $to_term;
    private $orgs;
    private $result;

    public function __construct()
    {
        $this->from_term = Term::getCurrentTerm();
        $this->predictToTerm();
        $this->load();
    }

    public function load()
    {
        $rollover_column = $this->getRolloverColumn();
        $db = new PHPWS_DB('sdr_organization_full');
        $db->addWhere('sdr_organization_full.term', $this->from_term);
        $db->addColumn('sdr_organization_full.id');
        $db->addColumn('sdr_organization_full.name');
        $db->addColumn("sdr_organization_full.$rollover_column", NULL, 'rollover');
        $db->addOrder('sdr_organization_full.name');

        $result = $db->select();
		if(PHPWS_Error::logIfError($result)) {
			PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
			throw new DatabaseException('Could not select rollover information.');
        }

        $this->orgs = $result;
    }

    protected function getRolloverColumn()
    {
        if(substr($this->from_term, 4, 2) == '10') {
            return 'rollover_stf';
        } else if(substr($this->from_term, 4, 2) == '40') {
            return 'rollover_fts';
        } else {
            PHPWS_Core::initModClass('sdr', 'exception/TermException.php');
            throw new TermException('Only Spring and Fall allowed in Rollover process.');
        }
    }

    protected function predictToTerm()
    {
        $year = substr($this->from_term, 0, 4);
        if(substr($this->from_term, 4, 2) == '10') {
            $this->to_term = $year . '40';
        } else if(substr($this->from_term, 4, 2) == '40') {
            $year++;
            $this->to_term = $year . '10';
        } else {
            PHPWS_Core::initModClass('sdr', 'exception/TermException.php');
            throw new TermException('Only Spring and Fall allowed in Rollover process.');
        }
    }

    public function getSettingsView()
    {
        $form = new PHPWS_Form('edit_rollover_settings');

        $cmd = CommandFactory::getCommand('SaveRolloverSettings');
        $cmd->setRolloverColumn($this->getRolloverColumn());
        $cmd->initForm($form);

        $checkboxes = array();
        foreach($this->orgs as $org) {
            $id = $org['id'];
            $name = $org['name'];
            $check = $org['rollover'];

            $box  = '<li><input type="checkbox" name="rollover['.$id.']" id="edit_rollover_settings_rollover_'.$id.'" title="'.$name.'"';
            if($check) $box .= ' checked="checked"';
            $box .= ' /><label class="checkbox-label" id="edit_rollover_settings_rollover_'.$id.'-label" for="edit_rollover_settings_rollover_'.$id.'">'.$name.'</label></li>';
            $checkboxes[] = $box;
        }

        $form->addSubmit('submit', 'Save Settings');

        $tpl = $form->getTemplate();
        $tpl['CHECKBOXES'] = implode($checkboxes, "\n");

        $tpl['FROM_TERM'] = Term::toString($this->from_term);
        $tpl['TO_TERM'] = Term::toString($this->to_term);

        return PHPWS_Template::process($tpl, 'sdr', 'EditRolloverSettings.tpl');
    }

    public function execute()
    {
        try {
        // Initialize Result Message Array
        $this->result = array();
        $type = $this->getRolloverColumn();

        // Some internal data that may be useful in debugging and unfucking
        $this->r('Rollover Process Began at ' . date('d/M/Y H:i:s'));
        $this->r("Rolling from {$this->from_term} to {$this->to_term}");
        $this->r("Roll type is $type");

        // Gather and report all organizations that will be rolled
        $rollover_orgs = array();
        $this->r("Organizations registered for {$this->from_term} and their rollover status:");
        foreach($this->orgs as $org) {
            // If it's not marked to roll, skip
            if($org['rollover'] != 1) {
                $this->r(" - ({$org['id']}) {$org['name']} - Skipped, unmarked");
                continue;
            }

            // If anyone is a member in the new term, skip
            $db = new PHPWS_DB('sdr_membership');
            $db->addWhere('organization_id', $org['id']);
            $db->addWhere('term', $this->to_term);
            $result = $db->count();
            if($result > 0) {
                $this->r(" - ({$org['id']}) {$org['name']} - Skipped, has memberships");
                continue;
            }

            $this->r(" + ({$org['id']}) {$org['name']} WILL ROLLOVER");
            $rollover_orgs[] = $org['id'];
        }

        $this->r('Rollover will now clear out unapproved membership requests.');
        // Clear out unapproved memberships
        $db = new PHPWS_DB('sdr_membership');
        $db->addWhere('sdr_membership.student_approved', 0, NULL, 'or', 'approve');
        $db->addWhere('sdr_membership.organization_approved', 0, NULL, 'or', 'approve');
        $db->addWhere('term', $this->from_term);
        $db->addColumn('id');
        $result = $db->select('col');

        if(count($result) > 0) {
            $this->r('There are ' . count($result) . ' unapproved membership requests.  Deleting roles...');
            $db = new PHPWS_DB('sdr_membership_role');
            $db->addWhere('membership_id', $result);
            $db->delete();
            
            $this->r('Deleting memberships...');
            $db = new PHPWS_DB('sdr_membership');
            $db->addWhere('id', $result);
            $db->delete();

            $this->r('Unapproved memberships have been cleared.');
        } else {
            $this->r('No unapproved memberships to clear!');
        }

        // Register Clubs

        PHPWS_Core::initModClass('sdr', 'Organization.php');
        PHPWS_Core::initModClass('sdr', 'OrganizationInstance.php');
        foreach($rollover_orgs as $org_id) {
            $db = new PHPWS_DB('sdr_organization_instance');
            $db->addWhere('organization_id', $org_id);
            $db->addWhere('term', $this->from_term);
            $oi = new OrganizationInstance();
            $db->loadObject($oi);
            
            $ni = clone($oi);
            $ni->id = null;
            $ni->term = $this->to_term;
            $ni->save();

            $this->r('Registered '.$ni->name.' for '.$this->to_term);
        }

        // Select memberships to roll
        $db = new PHPWS_DB('sdr_membership');
        $db->addJoin('left outer', 'sdr_membership', 'sdr_membership_role', 'id', 'membership_id');
        $db->addWhere('sdr_membership.organization_id', $rollover_orgs);
        $db->addWhere('sdr_membership.term', $this->from_term);
        $db->addColumn('sdr_membership.*');
        $db->addColumn('sdr_membership_role.*');
        $db->addOrder('sdr_membership.id');
        $result = $db->select();

        $this->r('Should roll '.count($result).' memberships');

        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'Membership.php');
        PHPWS_Core::initModClass('sdr', 'MembershipRole.php');

        // Roll that beautiful bean footage
        $last_member_id = -1;
        $membership = new Membership();
        foreach($result as $m) {
            if($last_member_id != $m['id']) {

                // Check out the Member.  If they're not an advisor and not registered for the
                // to_term, they cannot be a member.
                $member = new Member($m['member_id']);
                if(!$member->isAdvisor() && !$member->getStudent()->isRegistered($this->to_term)) {
                    $this->r("Skipping Membership: {$m['member_id']}, {$m['organization_id']}");
                    continue;
                }
                $last_member_id = $m['id'];

                // If we get this far, they're registered.
                $this->r("New Membership: {$m['member_id']}, {$m['organization_id']}");
                $membership = new Membership();
                $membership->setMemberId($m['member_id']);
                $membership->setOrganizationId($m['organization_id']);
                $membership->setStudentApproved(true);
                $membership->setOrganizationApproved(true);
                $membership->setAdministrator($m['administrator']);
                $membership->setTerm($this->to_term);
                $membership->setAdministrativeForce(1);
                $membership->save();
            }

            if(is_null($m['role_id'])) continue;

            $this->r(" + Role: " . $membership->getId() . ", {$m['role_id']}");
            $mr = new MembershipRole($membership->getId(), $m['role_id']);
            $mr->save();
        }

        $this->r("Looks like we're done here!");
        }catch(Exception $e) {
            test($e);

            $this->r('Crap');
        }

        exit();
    }

    public function formatResult($sep)
    {
        return implode($sep, $this->result);
    }

    protected function r($message)
    {
        echo "$message<br />\n";
        $this->result[] = $message;
    }
}

?>
