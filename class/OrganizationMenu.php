<?php

/**
 * SDR Organization Menu Controller
 * Displays a permission-sensitive menu for actions to be performed
 * on an organization.
 * 
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CommandMenu.php');
PHPWS_Core::initModClass('sdr', 'Organization.php');

class OrganizationMenu extends CommandMenu
{
    protected $org;

    public function __construct($org)
    {
        $this->org = $org;
        parent::__construct();
    }

    protected function setupCommands()
    {
        $commands = array();
        $org = $this->org;
		
        if(UserStatus::orgAdmin($org->getId())) {
            $commands['Roster'] = 'ShowOrganizationRoster';

            if(Term::isCurrentTermSelected()) {
                if($org->registeredForTerm(Term::getSelectedTerm())) {
                    $commands['Messaging'] = 'OrganizationMessagingCommand';
                }
            }

            $commands['View Profile'] = 'ShowOrganizationProfile';

            PHPWS_Core::initModClass('sdr', 'GlobalLock.php');
            if(!GlobalLock::isLocked() || UserStatus::isAdmin()){
                $commands['Edit Profile'] = 'EditOrganizationProfile';
            }
        }

        if(UserStatus::isAdmin()) {
            $commands['History'] = 'ShowOrganizationHistory';
            $commands['Settings'] = 'OrganizationSettings';
        }

        foreach($commands as $text=>$command) {
            $cmd = CommandFactory::getCommand($command);
            $cmd->setOrganizationId($org->getId());
            $this->addCommand($text, $cmd);
        }
    }
}

?>
