<?php

PHPWS_Core::initModClass('sdr', 'AngularViewCommand.php');
PHPWS_Core::initModClass('sdr', 'PDOFactory.php');

class ClubRegistrationFormCommand extends AngularViewCommand
{
    protected $registration_id;

    public function allowExecute()
    {
        return !UserStatus::isGuest();
    }

    public function getParams()
    {
        return array('registration_id');
    }

    public function getRawFile()
    {
        return 'ClubRegistration.html';
    }

    public function getJsVars()
    {
        return array('REGISTRATION_ID' => $this->registration_id);
    }

    public function setRegistrationId($id)
    {
        $this->registration_id = $id;
    }

    public function getURI($ajax = null)
    {
        if(is_null($this->registration_id)) {
            $this->registration_id = 'new';
            $ret = parent::getURI($ajax);
            $this->registration_id = null;
        } else {
            $ret = parent::getURI($ajax);
        }

        return $ret;
    }

    public function get(CommandContext $context)
    {
        if($this->registration_id == 'new') {
            throw new PermissionException('The Club Registration Form has been disabled for summer.');
        }
        return parent::get($context);
    }
}

?>
