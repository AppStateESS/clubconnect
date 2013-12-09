<?php

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');
PHPWS_Core::initModClass('sdr', 'Membership.php');

class OrganizationRequestMenu extends CommandMenu
{
    protected $org;

    public function __construct($org)
    {
        $this->org = $org;
        parent::__construct();
    }

    protected function setupCommands()
	{
        $org = $this->org;

		PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
		if(!GlobalLock::isLocked() || UserStatus::isAdmin()){
		
		  $mreq = CommandFactory::getCommand('RequestMembership');
		  $mreq->setOrganizationId($org->getId());
		  $this->addCommand('Request Membership', $mreq);
		
		  $ireq = CommandFactory::getCommand('ShowRequestInformation');
		  $ireq->setOrganizationId($org->getId());
		
		  $jqdialog = CommandFactory::getCommand('JQueryDialog');
		  $jqdialog->setViewCommand($ireq);
		  $jqdialog->setDialogTitle('Request Information about ' . $org->getName(false));
		
		  $this->addCommand('Request More Information', $jqdialog);
		}
	}
}

?>
