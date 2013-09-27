<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class SummaryPendingView extends sdr\View
{
    var $pending;

    public function __construct(array $pending)
    {
        $this->pending = $pending;
    }

    public function show()
    {
        $tpl = array();
        if(empty($this->pending)) {
            $tpl['NO_PENDING_MESSAGE'] = 'You have no pending club membership requests.';
        } else foreach($this->pending as $p) {
            $menu = new MembershipMenu($p);

            $ptpl = array();
            $ptpl['PENDING'] = $p->getOrganizationName();
            $ptpl['ACTIONS'] = $menu->show();
            $tpl['PENDING'][] = $ptpl;
        }

        return PHPWS_Template::process($tpl, 'sdr', 'SummaryPendingView.tpl');
    }
}

?>
