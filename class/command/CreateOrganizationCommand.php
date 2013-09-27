<?php

/**
 * Command class for creating a new organization.
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');

class CreateOrganizationCommand extends CrudCommand {

    protected $name;
    protected $type;
    protected $address;
    protected $bank;
    protected $ein;
    protected $managed;
    
    public function allowExecute()
    {
        return UserStatus::isAdmin() && UserStatus::hasPermission('club_admin');
    }

    public function get(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'CreateOrganizationView.php');
        $view = new CreateOrganizationView();

        $view->name    = $context->get('name');
        $view->type    = $context->get('type');
        $view->address = $context->get('address');
        $view->bank    = $context->get('bank');
        $view->ein     = $context->get('ein');
        $view->managed = $context->get('managed');

        $view->setSubmitCommand($this);
        
        $context->setContent($view->show());
    }
    
    public function post(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'Organization.php');

        $onError = $this;
        
        $name    = $context->get('name');
        $type    = $context->get('type');
        $address = $context->get('address');
        $bank    = $context->get('bank');
        $ein     = $context->get('ein');
        $managed = !is_null($context->get('student_managed'));

        $onError->name    = $name;
        $onError->type    = $type;
        $onError->address = $address;
        $onError->bank    = $bank;
        $onError->ein     = $ein;
        $onError->managed = $managed;
        
        // Make sure an org name was entered
        if(!isset($name) || empty($name)){
            NQ::Simple('sdr', 'SDR_NOTIFICATION_WARNING', 'Please specify a name.');
            $onError->redirect();
            return;
        }
        
        if(Organization::organizationExistsByName($name)){
            NQ::Simple('sdr', 'SDR_NOTIFICATION_WARNING', 'An organization by that name already exists.');
            $onError->redirect();
            return;
        }
        
        if(!isset($type) || empty($type)){
            NQ::Simple('sdr', 'SDR_NOTIFICATION_WARNING', 'Please specify a type.');
            $onError->redirect();
            return;
        }
        
        $org = new Organization();
        
        $org->setName($name);
        $org->setType($type);
        $org->setAddress($address);
        $org->setBank($bank);
        $org->setEin($ein);
        $org->setStudentManaged($managed);
        $org->setTerm(Term::getCurrentTerm());

        // Defaults... should probably be defined somewhere else
        $org->rollover_stf = false;
        $org->rollover_fts = true;
        $org->locked = false;

        $org->save();
        
        $success_cmd = CommandFactory::getCommand('ShowOrganizationRoster');
        $success_cmd->setOrganizationId($org->getId());
        $success_cmd->redirect();
    }
}

?>
