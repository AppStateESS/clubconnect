<?php

/**
 * SDR Form
 * Handles the very basic SDR Form.  It's essentially a view with some
 * logic that will populate an object based on what's submitted via the form.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class Form extends sdr\View
{
    public abstract function post(CommandContext &$context);
}

?>
