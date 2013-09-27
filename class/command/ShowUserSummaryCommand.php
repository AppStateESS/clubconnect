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

        /*
        $term = Term::getCurrentTerm();

        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        $memberships = MembershipFactory::getConfirmedMembershipsByUsername(
            UserStatus::getUsername(), $term);

        $pending = MembershipFactory::getPendingMembershipsByUsername(
            UserStatus::getUsername(), $term);

        PHPWS_Core::initModClass('sdr', 'Member.php');
        $member = new Member(NULL, UserStatus::getUsername());

        PHPWS_Core::initModClass('sdr', 'OrganizationApplicationFactory.php');
        $applications = OrganizationApplicationFactory::getApplicationsByUserId(
            $member->id, $term);

        PHPWS_Core::initModClass('sdr', 'SummaryMembershipsView.php');
        $membershipsView = new SummaryMembershipsView($memberships);

        PHPWS_Core::initModClass('sdr', 'SummaryPendingView.php');
        $pendingView = new SummaryPendingView($pending);

        PHPWS_Core::initModClass('sdr', 'SummaryApplicationsView.php');
        $applicationsView = new SummaryApplicationsView($applications);

        PHPWS_Core::initModClass('sdr', 'SummaryView.php');
        $view = new SummaryView($term);
        $view->addMain($membershipsView);
        $view->addSide($pendingView);
        $view->addSide($applicationsView);
         */

        $vars = array(
            'CLUBREG_LINK' => CommandFactory::getCommand('ClubRegistrationFormCommand')->getURI(),
            'TRANSCRIPT_LINK' => CommandFactory::getCommand('ShowUserTranscriptCommand')->getURI(),
            'CLUBDIR_LINK' => CommandFactory::getCommand('ClubDirectory')->getURI()
        );

        $context->setContent(
            PHPWS_Template::process($vars, 'sdr', 'SummaryView.tpl'));
	}
}
