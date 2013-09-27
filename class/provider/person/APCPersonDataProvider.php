<?php

namespace sdr\provider\person;
use \apc_store;
use \apc_fetch;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class APCPersonDataProvider extends PersonDataProvider
{
    protected $enabled;

    public function __construct(PersonDataProvider $fallbackProvider = null, $ttl = null)
    {
        parent::__construct($fallbackProvider, $ttl);
        $this->enabled = function_exists('apc_fetch');
    }

    public function getPersonByUsername($username)
    {
        if(!$this->enabled) {
            return $this->getFallbackProvider()->getPersonByUsername($username);
        }

        // Lookup Banner ID
        $result = apc_fetch($this->getResolveCacheKey($username));
        if($result === FALSE) {
            return $this->store($this->getFallbackProvider()->getPersonByUsername($username));
        }

        $result = apc_fetch($result);
        return $this->getPersonById($result);
    }

    public function getPersonById($id)
    {
        if(!$this->enabled) {
            return $this->getFallbackProvider()->getPersonById($id);
        }

        $result = apc_fetch($this->getCacheKey($id));
        if($result === FALSE) {
            return $this->store($this->getFallbackProvider()->getPersonById($id));
        }

        return $result;
    }

    protected function store(PersonMemento $memento)
    {
        apc_store($this->getResolveCacheKey($memento['username']), $memento['id']);
        apc_store($this->getCacheKey($memento['id']), $memento);
        return $memento;
    }

    protected function getCacheKey($id)
    {
        return "APCPersonDataProvider-$id";
    }

    protected function getResolveCacheKey($username)
    {
        return "APCPersonDataProvider-Resolver-$username";
    }
}

?>
