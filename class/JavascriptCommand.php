<?php

/**
 * SDR Command Javascript Interface
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

interface JavascriptCommand
{
	/**
	 * Gets a Javascript callback function for passing to another
	 * Javascript command
	 * 
	 * @return string The callback function
	 */
    function getJsCallback();
    
    /**
     * Gets the Javascript to be inserted
     * 
     * @return string The Javascript
     */
    function getJavascript();
}

?>
