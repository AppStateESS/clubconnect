<?php

namespace sdr\user;

/**
 * Description of User
 *
 * @author jtickle
 */
abstract class User
{
    public abstract function hasPermission(Command $command);
    public abstract function getUsername();
    public abstract function getId();
    public abstract function getPerson();
}

?>
