<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class CreateAdvisorForOrganizationApplicationCommand extends Command
{
    private $app_id;

    public function getRequestVars()
    {
        $vars = array('action' => 'CreateAdvisorForOrganizationApplication');

        if(!is_null($this->app_id)) {
            $vars['app_id'] = $this->app_id;
        }

        return $vars;
    }

    public function setApplicationId($id)
    {
        $this->app_id = $id;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('sdr', 'exception/PermissionException.php');
            throw new PermissionException('You must be an admin to create a new advisor record.');
        }

        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'Advisor.php');
        $m = new Member();
        $a = new Advisor();

        PHPWS_Core::initModClass('sdr', 'OrganizationApplication.php');
        $app = new OrganizationApplication($context->get('app_id'));

        // Try to guess a few things.
        $prefixes = array('ms.', 'miss', 'mrs.', 'mr.', 'rev.', 'dr.');
        $suffixes = array('jr', 'sr', 'i', 'ii', 'iii', 'iv');

        $name = explode(' ', $app->req_advisor_name);
        switch(count($name)) {
        case 1:
            $m->setLastName($name[0]);
            break;
        case 2:
            $m->setFirstName($name[0]);
            $m->setLastName($name[1]);
            break;
        case 3:
            if(in_array(strtolower($name[0]), $prefixes)) {
                $m->setPrefix($name[0]);
                $m->setFirstName($name[1]);
                $m->setLastName($name[2]);
            } else if(in_array(strtolower($name[2]), $suffixes)) {
                $m->setFirstName($name[0]);
                $m->setLastName($name[1]);
                $m->setSuffix($name[2]);
            } else {
                $m->setFirstName($name[0]);
                $m->setMiddleName($name[1]);
                $m->setLastName($name[2]);
            }
            break;
        case 4:
            if(in_array(strtolower($name[0]), $prefixes)) {
                $m->setPrefix($name[0]);
                $m->setFirstName($name[1]);
                $m->setMiddleName($name[2]);
                $m->setLastName($name[3]);
            } else if(in_array(strtolower($name[3]), $suffixes)) {
                $m->setFirstName($name[0]);
                $m->setMiddleName($name[1]);
                $m->setLastName($name[2]);
                $m->setSuffix($name[3]);
            } else {
                $m->setFirstName($name[0]);
                $m->setMiddleName($name[1] . ' ' . $name[2]);
                $m->setLastName($name[3]);
            }
            break;
        case 5:
            $m->setPrefix($name[0]);
            $m->setFirstName($name[1]);
            $m->setMiddleName($name[2]);
            $m->setLastName($name[3]);
            $m->setSuffix($name[4]);
            break;
        }

        $email = explode('@', $app->req_advisor_email);
        if(isset($email[0])) $m->setUsername($email[0]);

        $a->setDepartment($app->req_advisor_dept);
        $a->setOfficeLocation($app->req_advisor_bldg);
        
        if(substr($app->req_advisor_phone, 0, 3) == '262' || substr($app->req_advisor_phone, 0, 7) == '828-262')
            $a->setOfficePhone($app->req_advisor_phone);
        else
            $a->setCellPhone($app->req_advisor_phone);

        $m->setAdvisor($a);

        PHPWS_Core::initModClass('sdr', 'EditMemberView.php');
        $c = CommandFactory::getCommand('SaveAdvisorForOrganizationApplication');
        $c->setApplicationId($context->get('app_id'));
        $editView = new EditMemberView($m, $c);

        $tpl = array();

        $tpl['EDIT'] = $editView->show();
        $tpl['ADVISOR_NAME'] = $app->req_advisor_name;
        $tpl['ADVISOR_DEPT'] = $app->req_advisor_dept;
        $tpl['ADVISOR_BLDG'] = $app->req_advisor_bldg;
        $tpl['ADVISOR_PHONE'] = $app->req_advisor_phone;
        $tpl['ADVISOR_EMAIL'] = $app->req_advisor_email;

        $content = PHPWS_Template::process($tpl, 'sdr', 'AdvisorOrganization.tpl');

        $context->setContent($content);
    }
}

?>
