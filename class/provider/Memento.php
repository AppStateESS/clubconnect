<?php

namespace sdr\provider;

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class Memento implements \ArrayAccess
{
    protected $vars;

    public function __construct($from, $prefix = null)
    {
        if(is_object($from)) {
            $newfrom = array();
            foreach(get_object_vars($from) as $key) {
                $this->vars[$key] = $from->$key;
            }
            $from = $newfrom;
        }

        if(is_array($from)) {
            if($prefix) {
                $this->vars = array();
                foreach($from as $key => $val) {
                    if(!preg_match("/^$prefix/", $key)) continue;
                    $this->vars[preg_replace("/^$prefix/", '', $key)] = $val;
                }
            } else {
                $this->vars = $from;
            }
        } else {
            throw new \InvalidArgumentException($from);
        }
    }

    public function getKeys()
    {
        return array_keys($this->vars);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->vars);
    }

    public function offsetGet($offset)
    {
        return $this->vars[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new ImmutableMementoException();
    }

    public function offsetUnset($offset)
    {
        throw new ImmutableMementoException();
    }

    public function toIterator()
    {
        return new MementoIterator($this);
    }
}

?>
