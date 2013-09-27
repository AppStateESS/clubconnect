<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');
PHPWS_Core::initModClass('sdr', 'PDOFactory.php');

class RegistrationSearchClubsCommand extends CrudCommand
{
    public function get(CommandContext $context)
    {
        $pdo = PDOFactory::getInstance();

        $restrict = '';
        if(!UserStatus::isAdmin()) {
            $restrict = ' WHERE term IN (201240, 201310, 201340)';
        }
        
        $stmt = $pdo->prepare("SELECT id, banner_id, name AS fullname, address, bank, ein, term, student_managed FROM sdr_organization_recent$restrict");

        if(!$stmt->execute()) {
            PHPWS_Core::initModClass('sdr', 'JsonError.php');
            $error = new JsonError('500 Internal Server Error');
            $error->setMessage('An error occurred on the server. This error has been reported. Please try again later.');
            $error->setPersistent($stmt->errorInfo());
            $context->setContent($error->save());
            return;
        }

        $clubs = array();
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if(UserStatus::isAdmin()) {
                $profile = CommandFactory::getInstance()->get('ShowOrganizationRosterCommand');
            } else {
                $profile = CommandFactory::getInstance()->get('ShowOrganizationProfileCommand');
                $row['ein'] = !!$row['ein'];
            }
            $profile->setOrganizationId($row['id']);
            $row['url'] = array(
                'default' => $profile->getURI()
            );
            $clubs[] = $row;
        }

        $context->setContent($clubs);
    }
}

?>
