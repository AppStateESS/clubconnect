<?php

namespace sdr\provider;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class DataProvider
{
    protected $fallbackProvider;
    protected $ttl;

    const DEFAULT_TTL = 86400;

    public function __construct(PersonDataProvider $fallbackProvider = null, $ttl = null)
    {
        if(is_null($ttl)) {
            $this->ttl = PersonDataProvider::DEFAULT_TTL;
        } else {
            $this->ttl = $ttl;
        }

        $this->fallbackProvider = $fallbackProvider;
    }

    public abstract function clearCache();

    public final function clearAllCache()
    {
        $this->clearCache();
        if(!is_null($this->fallbackProvider)) {
            $this->fallbackProvider->clearAllCache();
        }
    }

    protected function getFallbackProvider()
    {
        if(is_null($this->fallbackProvider)) {
            throw new PersonNotFoundException();
        } else {
            return $this->fallbackProvider;
        }
    }
}

?>
