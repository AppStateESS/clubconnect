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
            $restrict = ' WHERE o.term IN (201240, 201310, 201340)';
        }
        
        $stmt = $pdo->prepare("
            SELECT o.id, o.banner_id, o.name AS fullname, o.address, o.bank, o.ein, o.term, o.student_managed, r.state
            FROM sdr_organization_recent AS o
            LEFT OUTER JOIN sdr_organization_registration_view_short AS r
                ON o.id = r.organization_id AND r.term IN (201340, 201410)
            $restrict");

        if(!$stmt->execute()) {
            PHPWS_Core::initModClass('sdr', 'exception/SdrPdoException.php');
            $e = new SdrPdoException('An error occurred on the server. Please try again later.');
            $e->setErrorInfo($stmt->errorInfo());
            throw $e;
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
