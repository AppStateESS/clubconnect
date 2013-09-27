<?php

namespace sdr\user;

/**
 * Description of GuestUser
 *
 * @author jtickle
 */
class GuestUser extends User
{
    protected $permissions;
    
    public function __construct(array $permissions)
    {
        $this->permissions = $permissions;
    }
    
    public function hasPermission(Command $command)
    {
        return in_array($command->getName(), $this->permissions);
    }
    
    public function getId()
    {
        throw new UserException('Guest user has no ID');
    }
    
    public function getPerson()
    {
        throw new PersonException('Guest user has no Person object');
    }
}

?>
