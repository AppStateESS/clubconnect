<?php

/**
 * Shows the messaging dialog
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class OrganizationMessagingCommand extends CrudCommand
{
    protected $organization_id;

    public function getParams()
    {
        return array('organization_id');
    }

    public function setOrganizationId($id)
    {
        $this->organization_id = $id;
    }

    public function get(CommandContext $context)
    {
        if(!isset($this->organization_id)) {
            $this->organization_id = $context->get('organization_id');
        }

        $orgid = $this->organization_id;

        if(!UserStatus::orgAdmin($orgid)) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to message the members of this organization.');
        }

        PHPWS_Core::initModClass('sdr', 'OrganizationManager.php');
        $orgManager = new OrganizationManager($orgid);

        $context->setContent($orgManager->showMessaging());
    }

    public function post(CommandContext $context)
    {
        if(!isset($this->organization_id)) {
            $this->organization_id = $context->get('organization_id');
        }

        $orgid = $this->organization_id;

        if(!UserStatus::orgAdmin($orgid)) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to message the members of this organization.');
        }

        PHPWS_Core::initModClass('sdr', 'MimeEmail.php');
        PHPWS_Core::initModClass('sdr', 'MembershipFactory.php');
        PHPWS_Core::initModClass('sdr', 'Member.php');

        $msg = new MimeEmail();
        $msg->setSubject($context->get('mail_subject'));
        $msg->setBody($context->get('mail_body'));

        $sender = new Member(null, UserStatus::getUsername());
        if($sender->getId()) {
            $msg->setSenderMember($sender);
        } else {
            $msg->setSender(UserStatus::getUsername() . '@appstate.edu');
        }

        $memberships = MembershipFactory::getMembershipsByOrganization($orgid,
            Term::getCurrentTerm());
        foreach($memberships as $membership) {
            $msg->addRecipientMember($membership->getMember());
        }

        $msg->send();

        $context->goBack();
    }
}
