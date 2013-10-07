<?php

/**
 * Shows the main User Summary
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class ShowUserSummaryCommand extends Command
{
	function getRequestVars()
	{
		return array('action' => 'ShowUserSummary');
	}
	
	
	function execute(CommandContext $context)
	{
		if(!UserStatus::isUser()) {
			PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
			throw new PermissionException('You must be logged in to view the User Summary.');
		}

        $vars = array();

        $term = Term::getCurrentTerm();
        $username = UserStatus::getUsername();

        // Load Clubs
        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        $memberships = MembershipFactory::getConfirmedMembershipsByUsername(
            $username, $term);

        if(empty($memberships)) {
            $vars['TRANSCRIPT_LINK'] =
                CommandFactory::getInstance()->get('ShowUserTranscriptCommand')->getURI();
            $vars['CLUBDIR_LINK'] =
                CommandFactory::getInstance()->get('ClubDirectory')->getURI();
        } else {
            $vars['MEMBERSHIPS'] = array();
            $profile = CommandFactory::getInstance()->get(
                'ShowOrganizationProfile', array('organization_id' => null));
            foreach($memberships as $m) {
                $org = $m->getOrganization();
                $profile->setOrganizationId($org->getId());
                $vars['MEMBERSHIPS'][] = array(
                    'NAME' => $org->getName(false),
                    'URL'  => $profile->getURI()
                );
            }
        }

        // Load Notifications
        $vars['NOTIFICATIONS'] = array();

        // Outstanding Officer Requests
        PHPWS_Core::initModClass('sdr', 'OfficerRequestController.php');
        $orctrl = new OfficerRequestController();
        $ors = $orctrl->get(null, UserStatus::getUsername());
        $agree = CommandFactory::getInstance()->get(
            'OfficerRequestAgreementCommand', array('offreq_id' => null));
        foreach($ors as $or) {
            if(!is_null($or['officers'][0]['fulfilled'])) continue;
            $org = new Organization($or['organization_id'], $term);
            $agree->setOfficerRequestId($or['officer_request_id']);
            $vars['NOTIFICATIONS'][] = array(
                'TITLE' => 'Club Registration',
                'TEXT'  => 'Confirm your involvement in <strong>'.$org->getName(false).'</strong> to proceed.',
                'URL'   => $agree->getURI()
            );
        }

        // Membership Requests
        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        $mrs = MembershipFactory::getPendingMembershipsByUsername(
            UserStatus::getUsername(),
            Term::getCurrentTerm());
        $accept = CommandFactory::getInstance()->get(
            'AcceptMembershipCommand', array('membership_id' => null));
        var_dump($mrs);
        foreach($mrs as $mr) {
            $accept->setMembershipId($mr->getId());
            $vars['NOTIFICATIONS'][] = array(
                'TITLE' => 'Membership Request',
                'TEXT'  => '<strong>'.$mr->getOrganizationName(false).'</strong> has requested that you become a member.',
                'URL'   => $accept->getURI()
            );
        }
        // TODO: Administrative Club Membership Processing
        //
        if(empty($vars['NOTIFICATIONS'])) {
            unset($vars['NOTIFICATIONS']);
            $vars['NO_NOTIFICATIONS'] = 'You have no new notifications.';
        }

        $context->setContent(
            PHPWS_Template::process($vars, 'sdr', 'SummaryView.tpl'));
	}
}
