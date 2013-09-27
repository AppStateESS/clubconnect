<?php

/**
 * SDR Organization Profile View
 * Shows the Organization Profile.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Organization.php');
PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');

class OrganizationProfileView extends sdr\View
{
    private $organizationProfile;
    private $editCommand;
    private $backLink;
    private $requestCommand;
    private $suppressTitle;

    public function __construct(OrganizationProfile $organization)
    {
        $this->organizationProfile = $organization;
        $this->suppressTitle = FALSE;
    }

    public function setEditCommand(Command $edit)
    {
        $this->editCommand = $edit;
    }

    public function setBackLink($link)
    {
        $this->backLink = $link;
    }

    public function setRequestCommand(Command $request)
    {
        $this->requestCommand = $request;
    }

    public function show()
    {
        $org = $this->organizationProfile;

        $tpl = array();

        $options = $this->getOptions();

        if(isset($this->backLink))
        $tpl['BACKLINK'] = $this->backLink;

        if(!is_null($options) && !empty($options))
            $tpl['OPTIONS'] = $options;

        $club = $this->organizationProfile->getOrganization();
        $tpl['TITLE']    = $club->getName(false);
        $tpl['SUBTITLE'] = $club->getCategory();
        $menu = new OrganizationMenu($club);
        $tpl['MENU']     = $menu->show();

        if(!UserStatus::isGuest() && UserStatus::orgAdmin($org->getId())) {
            $tpl['TERM'] = Term::getTermSelector();

            if(!Term::isCurrentTermSelected()) {
                if(UserStatus::isAdmin()) {
                    $warning = 'You are currently working in a historical term.  Any changes made here will be applied to the selected term and students will be notified via the email address we have on record.  To work in the current term, please select it from the dropbox above.';
                } else {
                    $warning = 'You are currently viewing a historical term.  You will not be able to make changes to this roster.  Please contact CSIL to make historical changes.  To work in the current term, please select it from the dropbox above.';
                }
            }
        }

        if(is_null($org->getId())) {
            $tpl['NO_PROFILE'] = dgettext('sdr', 'No profile was found for this organization.');

            return PHPWS_Template::process($tpl, 'sdr', 'OrganizationNoProfileView.tpl');
        } else {
            $tpl['DATE']            = $org->getMeetingDate();
            $tpl['LOCATION']        = $org->getMeetingLocation();
            $tpl['PURPOSE']         = $org->getPurpose();
            $tpl['DESCRIPTION']     = $org->getDescription();
            $tpl['WEB_ADDRESS']     = $org->getLink();
            $tpl['CONTACT_EMAIL']    = $this->getContactInfo();
            if(!is_null($org->getClubLogo())) {
                $tpl['LOGO']            = '<img src="' . $org->getClubLogo() . '">';
            }
            if(!is_null($options) && !empty($options))
            $tpl['ID'] = $org->getOrganizationId();

            return PHPWS_Template::process($tpl, 'sdr', 'OrganizationProfileView.tpl');
        }
    }

    protected function getOptions()
    {
        if(UserStatus::isGuest()) {
        	return UserStatus::getBigLogin(dgettext('sdr', 'Please log in to request membership in an organization.'));
        }

        if(UserStatus::isAdmin()) {
            // No actions for the admin.
            return;
        }

        $org = $this->organizationProfile->getOrganization();
        $term = $org->getTerm();
        if($term != Term::getCurrentTerm()) {
            $ireq = CommandFactory::getCommand('ShowRequestInformation');
            $ireq->setOrganizationId($org->getId());
            $jqdialog = CommandFactory::getCommand('JQueryDialog');
            $jqdialog->setViewCommand($ireq);
            $jqdialog->setDialogTitle('Request Information about ' . $org->getName(false));
            $cmd = $jqdialog->getLink('Request More Information');
            return "<ul><li>This club is currently not registered; please check back later to request membership.</li><li>$cmd</li></ul>";
        }

        $membership = $this->organizationProfile->getOrganization()->getMembership(UserStatus::getUsername());
        if(is_null($membership)) {
            PHPWS_Core::initModClass('sdr', 'OrganizationRequestMenu.php');
            $menu = new OrganizationRequestMenu($this->organizationProfile->getOrganization());
            return $menu->show();
        } else {
            PHPWS_Core::initModClass('sdr', 'MembershipMenu.php');
            $menu = new MembershipMenu($membership);
            return $menu->show();
        }

        return NULL;
    }

    public function getRequestForm($org, $term=null)
    {
        if(is_null($term))
        $term = Term::getCurrentTerm();

        $form = new PHPWS_Form('request_membership');

        $cmd = CommandFactory::getCommand('RequestMembership');
        $cmd->setOrganization($org->getId());
        $cmd->initForm($form);

        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        $membership = MembershipFactory::getUserMembershipByOrganization($org->getId(), UserStatus::getUsername(), $term);

        if(!UserStatus::isGuest()){
            return;
        } else if(is_null($membership) || sizeof($membership) == 0){
            $form->addSubmit('Request Membership');
        } else if(!$membership->isConfirmedMember()){
            $form->addSubmit('Cancel Pending Request');
        } else {
            $form->addSubmit('Withdraw Membership');
        }

        return implode('', $form->getTemplate());
    }

    /**
     * Returns a array of the email addresses of the organization admins
     * @return unknown_type
     */
    private function getContactInfo()
    {
        $memberships = MembershipFactory::getAdminMembershipsByOrganization($this->organizationProfile->getOrganizationId(), Term::getCurrentTerm());

        $admins = array();
        $logged_in = !UserStatus::isGuest();

        foreach($memberships as $mbr){
            $name = $mbr->getMemberName();
            $email = $mbr->getMemberUsername();
            $role = $mbr->getRolesConcat();

            if($logged_in && isset($email) && !is_null($email)){
                $email .= '@appstate.edu'; // must append this after the above test
                $email_link = '<a href="mailto:' . $email .'">' . $name . '</a>';
                $admins[] = array('ROLE'=>$role, 'NAME'=>$email_link);
            }else{
                $admins[] = array('ROLE'=>$role, 'NAME'=>$name);
            }
        }

        return $admins;
    }
}

?>
