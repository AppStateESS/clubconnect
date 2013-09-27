<?php

use \sdr\activitylog\ActivityLog;

/**
 * SDR Command
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class Command
{
    private $logger;
    
    private $logOrg    = null;
    private $logMember = null;
    private $logNotes  = null;
    
    abstract function execute(CommandContext $context);

    /**
     * Initializes a {@link PHPWS_Form} with hidden values such that
     * it will properly call this command when submitted.
     *
     * Make sure that if you're going to set any member variables, you
     * do it before running initForm, as it calls
     * {@link getRequestVars} and sets what variables are available at
     * call time.
     *
     * @param PHPWS_Form &$form The form to be initialized
     * @see getRequestVars
     * @see getLink
     * @see getURI
     * @see redirect
     */
    public function initForm(PHPWS_Form &$form)
    {
        $uri = CommandFactory::getUriByCommand($this);

        // If no URI, do it the old fashioned way
        if($uri === FALSE) {
        	$form->addHidden('module', 'sdr');
        	foreach($this->getRequestVars() as $key=>$val) {
        		$form->addHidden($key, $val);
        	}
            return;
        }

        $form->setAction($uri);
    }

    /**
     * Returns the absolute URI to this command.  If you want to create
     * a proper HTML link to this command, you may want to look at
     * {@link getLink} instead.
     *
     * Make sure that if you're going to set any member variables, you
     * do it before running getURI, as it calls {@link getRequestVars}
     * and sets what variables are available at call time.
     *
     * @return string The absolute URI to this command
     * @see getRequestVars
     * @see getLink
     * @see initForm
     * @see redirect
     */
    public function getURI($ajax = null){
        $uri = CommandFactory::getInstance()->reverseMap($this);
        if($uri !== FALSE)
            return is_null($ajax) ? $uri : "$uri?ajax=$ajax";

        $uri = $_SERVER['SCRIPT_NAME'] . "?module=sdr";
        foreach($this->getRequestVars() as $key=>$val) {
            $uri .= "&$key=$val";
        }
        if(!is_null($ajax)) {
            $uri .= "&ajax=$ajax";
        }
        
        return $uri;
    }
    
    /**
     * Returns a properly formatted link to this command that can be
     * outputted straight to the browser.  If you want just the URL
     * instead of a properly formatted HTML link, have a look at
     * {@link getURI} instead.
     *
     * Make sure that if you're going to set any member variables, you
     * do it before running getLink, as it calls {@link getRequestVars}
     * and sets what variables are available at call time.
     *
     * @param string $text The text to format as a link
     * @param string $target The target of the link - See PHPWS_Text class.
     * @param string $cssClass The "class" (css) of the link.
     * @param string $title The alt-text for the link.
     * @return string The formatted link
     * @see getRequestVars
     * @see getURI
     * @see initForm
     * @see redirect
     * @see PHPWS_Text
     */
    public function getLink($text = NULL, $target = NULL, $cssClass = NULL, $title = NULL)
    {
        if(is_null($text)) $text = $this->getName();

        $link = '<a href="' . $this->getURI() . '"';
        if($target)   $link .= ' target="' . $target   . '"';
        if($cssClass) $link .=  ' class="' . $cssClass . '"';
        if($title)    $link .=  ' title="' . $title    . '"';
        $link .= '>' . $text . '</a>';

        return $link;
    }

    /**
     * Returns a 303 Redirect to this command and then exits.  This
     * should be used after every POST request or destructive GET to
     * prevent accidental damage through a refresh or back/forward
     * operation in the client web browser.
     *
     * Note: Obviously, the implementation of HTTP 303 is browser-
     * specific and cannot be predicted by this script.  Most browsers
     * implement HTTP 303 in such a way that refresh/back/forward won't
     * attempt to re-POST, but some might.  Sucks to be them.
     *
     * Also Note: This DOES EVENTUALLY CALL exit().  After you call
     * this redirect function, you won't be returned control unless an
     * exception is somehow thrown.
     *
     * @see getRequestVars
     * @see getLink
     * @see getURI
     * @see initForm
     */
    public function redirect()
    {
    	$path = $this->getURI();
    	NQ::close();
    	
    	header('HTTP/1.1 303 See Other');
    	header("Location: $path");
        SDR::quit();
    }

    /**
     * Tests to see if the *current user* is allowed to *execute* this
     * command.  Unfortunately, does not allow specification of a
     * different user, due to phpWebSite limitations.  This does not
     * control whether or not a user can *see* this command (ie. in a
     * link), for that see allowView.
     *
     * @return boolean True if the user can execute, False otherwise.
     *
     * @see allowView
     */
    public function allowExecute()
    {
        return true;
    }

    /**
     * Tests to see if the *current user* is allowed to *view* this
     * command, like in a link.  See docs for allowExecute for an
     * explanation of limitations.  By default, this just returns
     * whatever allowExecute returns, but that functionality can be
     * overridden in subclasses.
     *
     * @return boolean True if the user can view, False otherwise.
     *
     * @see allowExecute
     */
    public function allowView()
    {
        return $this->allowExecute();
    }

    /**
     * Override this if you want a pretty name to be provided to
     * menus and links and such... otherwise, the class name will
     * be used.  Of course, if you never link to this command from
     * a menu or something, it doesn't matter.  Yeah, bad OOP, I
     * know, get over it.
     *
     * @return string A friendly name for this command
     *
     * @see getIconClass
     */
    public function getName()
    {
        return $this->getAction();
    }

    /**
     * Override this if you want a pretty icon to be used,
     * otherwise no icon class will be returned.  Bad OOP again.
     *
     * @return string The icon class to use (NOT the icon size!)
     *
     * @see getName
     */
    public function getIconClass()
    {
        return '';
    }

    /**
     * Override this if you have a notification count, like if 5
     * items under this command need addressing, make it return 5.
     * The views will handle what to do with this data, but
     * generally, if they see 0 or null, they will either not show
     * the count or show some kind of "good job you're done"
     * type of message.
     *
     * @return int The number of items that need handling
     */
    public function getCount()
    {
        return 0;
    }

    /**
     * Gets the action name, which of course is the class name
     * without 'Command' on the end of it.
     *
     * @return string The action name for this command.
     */
    public final function getAction()
    {
        return get_class($this);
    }

    /**
     * Get the parameters that this command is expecting from the
     * request.  These parameters are to be considered "required".
     * By default, returns an empty array.
     *
     * @return array A string list of parameters
     */
    public function getParams()
    {
        return array();
    }

    /**
     * Returns an array of "required" parameters and their values
     * for seeding into a form or URI or something.
     *
     * @return array An associative array of parameters to values
     */
    public function getParamValues()
    {
        $ret = array();
        foreach($this->getParams() as $param) {
            $ret[$param] = $this->$param;
        }
        return $ret;
    }

    /**
     * Sets "required" parameters by associative array.
     *
     * @param array $vals The array to pull values from.
     */
    public function setParamValues(array $vals)
    {
        foreach($this->getParams() as $param) {
            $this->$param = $vals[$param];
        }
    }
    

    /**
     * For compatibility, automatically merges 'getParamValues'
     * and 'getAction' into a request string that can be used
     * in legacy parts of the application
     *
     * @return array The array from getParamValues with an extra
     *               action parameter added, basically
     */
    public function getRequestVars()
    {
        // TODO: Eliminate action hack
        $ret = $this->getParamValues();
        $ret['action'] = $this->getAction();
        return $ret;
    }
    
    public function setLogger(ActivityLog $log)
    {
        $this->logger = $log;
    }
    
    public function getLogger()
    {
        return $this->logger;
    }
    
    public function setLogOrganization(Organization $org)
    {
        $this->logOrg = $org;
    }
    
    public function setLogMember(Member $member)
    {
        $this->logMember = $member;
    }
    
    public function addLogNote($note)
    {
        $this->logNotes .= "$note\n";
    }
    
    public function log()
    {
        $this->getLogger()->log($this, $this->logOrg, $this->logMember,
                $this->logNotes);
    }
}

?>
