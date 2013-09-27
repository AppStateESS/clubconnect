<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'View.php');

class SummaryMembershipsView extends sdr\View
{
    var $memberships;

    public function __construct(array $memberships)
    {
        $this->memberships = $memberships;
    }

    public function show()
    {
        PHPWS_Core::initModClass('sdr', 'MembershipMenu.php');
        $tpl = array();
        if(empty($this->memberships)) {
            PHPWS_Core::initModClass('sdr', 'NoMembershipsMenu.php');
            $menu = new NoMembershipsMenu();
            $tpl['NO_MEMBERSHIPS_HEAD']    = dgettext('sdr', 'No memberships found for this term.');
            $tpl['NO_MEMBERSHIPS_SUBHEAD'] = dgettext('sdr', 'You are not involved with any organizations this semester.  Here are some things to do:');
            $tpl['NO_MEMBERSHIPS_MENU']    = dgettext('sdr', $menu->show());
        } else foreach($this->memberships as $m) {
            $menu = new MembershipMenu($m);

            $mtpl = array();
            $mtpl['NAME']    = $m->getOrganizationName();
            $mtpl['ACTIONS'] = $menu->show();
            $mtpl['TYPE']    = $this->cssType($m);
            if($m->isAwaitingApproval()) {
                $mtpl['POSITIONS'] = dgettext('sdr', 'Request Pending');
            } else {
                $mtpl['POSITIONS'] = $m->getRolesConcat();
            }
            $tpl['MEMBERSHIPS'][] = $mtpl;
        }

        return PHPWS_Template::process($tpl, 'sdr', 'SummaryMembershipsView.tpl');
    }
    
    protected function cssType(Membership $membership)
    {
    	switch($membership->getLevel()) {
    		case MBR_LEVEL_AWAITING_STUDENT:
    			return 'ssv-awaiting-student';
    		case MBR_LEVEL_AWAITING_ORG:
    			return 'ssv-awaiting-org';
    		default:
    			if($membership->isAdministrator())
    			    return 'ssv-admin';
    	}
    	
    	return 'ssv-member';
    }
}

?>
