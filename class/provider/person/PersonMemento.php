<?php

namespace sdr\provider\person;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PersonMemento extends sdr\provider\Memento
{
    public function __construct($id, $username, $email, $firstName,
        $middleName, $lastName, $prefix, $suffix, $preferred)
    {
        parent::__construct(array(
            'id'          => $id,
            'username'    => $username,
            'email'       => $email,
            'first_name'  => $firstName,
            'middle_name' => $middleName,
            'last_name'   => $lastName,
            'prefix'      => $prefix,
            'suffix'      => $suffix,
            'preferred'   => $preferred
        ));
    }
}

?>
