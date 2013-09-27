<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class TestView extends sdr\View
{
    var $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function show()
    {
        return $this->value;
    }
}

?>
