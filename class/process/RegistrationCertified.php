<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
PHPWS_Core::initModClass('sdr', 'Organization.php');
PHPWS_Core::initModClass('sdr', 'OrganizationInstance.php');
PHPWS_Core::initModClass('sdr', 'OrganizationProfile.php');
PHPWS_Core::initModClass('sdr', 'Member.php');
PHPWS_Core::initModClass('sdr', 'Membership.php');
PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
PHPWS_Core::initModClass('sdr', 'Role.php');

class RegistrationCertified
{
    public function execute(array $reg) {
        // Hopefully this actually does transactions lol
        $db = new PHPWS_DB();
        $db->query('BEGIN');

        // Create Instance, if necessary
        $org = new Organization($reg['organization_id'], $reg['term']);
        if(!$org->instance_id || $org->getTerm() != $reg['term']) {
            $inst = new OrganizationInstance();
            $inst->setOrganizationId($reg['organization_id']);
            $inst->setName($reg['fullname']);
            $inst->setShortName($reg['shortname']);
            $inst->setType(21); // TODO: Entirely replace "Type" system
            $inst->setTerm($reg['term']);
            $inst->setAddress($reg['address']);
            $inst->setBank($reg['bank']);
            $inst->setEin($reg['ein']);
            $inst->save();
        }

        // Update or Create Profile
        $profile = OrganizationProfile::getByOrganizationId($reg['organization_id']);
        $profile->setPurpose($reg['purpose']);
        $profile->setMeetingDate($reg['meetings']);
        $profile->setMeetingLocation($reg['location']);
        $profile->setDescription($reg['description']);
        $profile->setSiteUrl($reg['website']);
        $profile->setRequirements($reg['requirements']);

        $db = new PHPWS_DB('sdr_organization_profile');
        $result = $db->saveObject($profile);

        // Administrative Officers get auto-membership
        $orctrl = new OfficerRequestController();
        list($req) = $orctrl->get($reg['officer_request_id']);

        $mgr = new OrganizationManager($reg['organization_id']);

        $adminMembers = array();
        $regularMembers = array();

        $emails = array();
        foreach($req['officers'] as $officer) {
            $membership = MembershipFactory::getMembershipByOrganizationMember(
                $reg['organization_id'],
                $officer['member_id'],
                $reg['term']
            );
            if(count($membership) != 0) continue;   // Already a member

            if($officer['role_id'] == 53 || $officer['role_id'] == 34) {
                $officer['admin'] = 1;
            }
            if($officer['admin']) {
                $membership = $mgr->addMember(new Member($officer['member_id']), $reg['term'], 1, 1, false, $officer['role_id']);
                $membership->setAdministrator(1);
                $membership->save();
                $emails[] = $officer['person_email'] . '@appstate.edu';
            } else {
                if($officer['member_id']) {
                    $member = new Member($officer['member_id']);
                } else {
                    $member = new Member(null, $officer['person_email']);
                }
                $membership = $mgr->addMember($member, $reg['term'], 0, 1, false, $officer['role_id']);
                $membership->save();
            }
        }

        $db->query('COMMIT');

        PHPWS_Core::initModClass('sdr', 'FullyApprovedApplicationEmail.php');
        $email = new FullyApprovedApplicationEmail(
            $emails,
            Term::toString($reg['term']),
            $reg['fullname'],
            $reg['organization_id']
        );
    }
}

?>
