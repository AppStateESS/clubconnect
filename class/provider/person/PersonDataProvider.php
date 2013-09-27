<?php

namespace sdr\provider\person;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class PersonDataProvider extends sdr\provider\DataProvider
{
    public abstract function getPersonByUsername($username);
    public abstract function getPersonById($id);
}

?>
