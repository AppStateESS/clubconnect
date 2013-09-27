<?php

/**
 * Shows a confirmation screen where the user can enter
 * a reason for kicking someone out of an organization.
 *
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'RemoveMembershipType.php');

class RemoveConfirmationView extends sdr\View
{
	private $membership;
	private $remove;
	private $type;
	
	public function __construct(Membership $membership, RemoveMembershipCommand $remove)
	{
		$this->membership = $membership;
		$this->remove = $remove;
		$this->type = new RemoveMembershipType($membership);
	}
	
	public function show()
	{
		PHPWS_Core::initModClass('sdr', 'Member.php');
		$member = new Member($this->membership->getMemberId());
		
		PHPWS_Core::initModClass('sdr', 'Organization.php');
		$org = new Organization($this->membership->getOrganizationId());
		
		$form = new PHPWS_Form('remove_membership_confirmation');
		$this->remove->initForm($form);
		$form->addText('reason');
		$form->setLabel('reason', dgettext('sdr', 'Please specify a reason (Optional)'));
		$form->setSize('reason', 40);
		$form->addSubmit('submit', $this->type->getSubmit());
		
		// TODO: a 'Do Nothing' button
		
		$tpl = $form->getTemplate();
        
        $tpl['QUESTION'] = $this->type->getQuestion();
        $tpl['ORG_LABEL'] = dgettext('sdr', 'Organization');
        $tpl['ORG'] = $org->getName(false);
        if($this->type->isOrg()) {
            $tpl['STUDENT_LABEL'] = dgettext('sdr', 'Student');
            $tpl['STUDENT'] = $member->getFullName();
        }
        
        return PHPWS_Template::process($tpl, 'sdr', 'Confirm.tpl');
	}
}

?>
