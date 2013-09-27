<?php

namespace sdr\provider\person;
use \PHPWS_DB;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PHPWSDBPersonDataProvider extends PersonDataProvider
{
    public function getPersonByUsername($username)
    {
        $db = new PHPWS_DB('sdr_member');
        $db->addWhere('username', $username);

        $result = $this->select($db);

        if(count($result) < 1) {
            return $this->getFallbackProvider()->getPersonByUsername($username);
        }

        return $this->createMemento($result);
    }

    public function getPersonById($id)
    {
        $db = new PHPWS_DB('sdr_member');
        $db->addWhere('id', $id);
        $result = $db->select();

        if(count($result) < 1) {
            return $this->getFallbackProvider()->getPersonByUsername($username);
        }

        return $this->createMemento($result);
    }

    protected function select(PHPWS_DB $db)
    {
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result);
        }

        return $result;
    }

    protected function createMemento($result)
    {
        return new PersonMemento(
            $result['id'],
            $result['username'],
            $result['username'] . '@appstate.edu',
            $result['first_name'],
            $result['middle_name'],
            $result['last_name'],
            $result['prefix'],
            $result['suffix'],
            null
        );
    }
}

?>
