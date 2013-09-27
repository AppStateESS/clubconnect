<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'CrudCommand.php');
PHPWS_Core::initModClass('sdr', 'PDOFactory.php');

class RegistrationGetRolesCommand extends CrudCommand
{
    public function get(CommandContext $context)
    {
        $pdo = PDOFactory::getInstance();

        $stmt = $pdo->prepare("SELECT id, title FROM sdr_role WHERE hidden = 0 AND rank > 0 ORDER BY title");

        if(!$stmt->execute()) {
            PHPWS_Core::initModClass('sdr', 'JsonError.php');
            $error = new JsonError('500 Internal Server Error');
            $error->setMessage('An error occurred on the server. This error has been reported. Please try again later.');
            $error->setPersistent($stmt->errorInfo());
            $context->setContent($error->save());
            return;
        }

        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $context->setContent($result);
    }
}

?>
