<?php

  /**
   * SDR User Membership Menu Controller
   *
   * Displays a contextual menu of actions that users can perform on their
   * memberships.
   *
   * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
   */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');
PHPWS_Core::initModClass('sdr', 'Membership.php');

class MembershipMenu extends CommandMenu
{
    protected $membership;

    public function __construct($membership)
    {
        $this->membership = $membership;
        parent::__construct();
    }

    protected function setupCommands()
    {
        $membership = $this->membership;
        if(UserStatus::orgAdmin($membership->getOrganizationId())) {
            $manage = CommandFactory::getCommand('ShowOrganizationRoster');
            $manage->setOrganizationId($membership->getOrganizationId());
            $this->addCommand('Manage Organization', $manage);
        }

        if($membership->getLevel() == MBR_LEVEL_AWAITING_ORG) {
            $cancel = CommandFactory::getCommand('RemoveMembershipConfirmation');
            $cancel->setMembershipId($membership->getId());
            $this->addCommand('Cancel Membership Request', $cancel);
        } else 
        if($membership->getLevel() == MBR_LEVEL_AWAITING_STUDENT) {
            $accept = CommandFactory::getCommand('AcceptMembership');
            $accept->setMembershipId($membership->getId());
            $this->addCommand('Accept Membership Request', $accept);
            $decline = CommandFactory::getCommand('RemoveMembershipConfirmation');
            $decline->setMembershipId($membership->getId());
            $this->addCommand('Decline Membership Request', $decline);
        } else {
            $remove = CommandFactory::getCommand('RemoveMembershipConfirmation');
            $remove->setMembershipId($membership->getId());
            $this->addCommand('Remove Membership', $remove);
        } 

        // TODO: Make this happen someday
        /*if(UserStatus::orgAdmin($membership->getOrganizationId(), null, 'ALL')) {
            $register = CommandFactory::getCommand('ShowOrganizationApplication');
            $register->setOrganizationId($membership->getOrganizationId());
            $this->addCommand('Register for ' . Term::getPrintableCurrentTerm(), 
            $register);
        }*/
    }
}

?>
