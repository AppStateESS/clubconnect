<?php

namespace sdr\provider;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class MementoIterator implements Iterator
{
    protected $memento;
    protected $position;
    protected $keys;

    public function __construct(Memento $memento)
    {
        $this->memento = $memento;
        $this->keys    = $memento->getKeys();
    }

    protected function toKey()
    {
        return $this->keys[$this->position];
    }

    public function current()
    {
        return $this->memento->offsetGet($this->toKey());
    }

    public function key()
    {
        return $this->toKey();
    }

    public function next()
    {
        $this->position++;
    }

    public function rewind()
    {
        $this->position--;
    }

    public function valid()
    {
        return $this->position > 0 && $this->position < count($this->keys);
    }
}

?>
