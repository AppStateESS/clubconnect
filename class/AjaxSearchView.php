<?php

/**
 * View class for the generalized AjaxSearch widget
 * @author jbooker
 * @package sdr
 */

define('AJAX_SEARCH_DELAY_DEFAULT', 750);

class AjaxSearchView extends \sdr\View {

    private $elementId;
    private $searchDelay;

    private $queryCommand;
    private $searchWhenEmpty;

    private $renderJSCallback;

    private $viewCommand;
    private $viewCommandKey;
    private $itemSelectedJSCallback;
    private $fieldsSetOnSelect;

    public function __construct()
    {
        $this->fieldsSetOnSelect = array();
    }
    
    /**
     * Sets the HTML element ID which the Javascript components
     * of this AjaxSearch should use. A HTML div object with the
     * given id must be present in the final document.
     * @param string $id Element id of the DIV tag to use
     */
    public function setElementId($id){
        $this->elementId = $id;
    }
    
    /**
     * Sets the time (in milliseconds) to wait after the user enters text
     * into the search box before submitting the AJAX request for the search
     * @param $delay int The delay between sending search requests
     */
    public function setSearchDelay($delay)
    {
        $this->searchDelay = $delay;
    }

    /**
     * Sets the command to run on the server when making an AJAX request for search results
     * @param Command $queryCmd The command to run on the server to get search results
     */
    public function setQueryCommand(Command $queryCmd)
    {
        $this->queryCommand = $queryCmd;
    }

    /**
     * Sets whether or not the Javascript widget will run a search on empty input
     * Set to 'true' or 'false'
     * @param string $search
     */
    public function setSearchWhenEmpty($search)
    {
        $this->searchWhenEmpty = $search;
    }
    
    /**
     * Specifies a Javascript function that should be invoked to render all of the
     * results returned from the AJAX request to the queryCommand. Must be a valid Javascript
     * function.
     * @param String $callbackFunction The JS function to render search results
     */
    public function setRenderJSCallback($callbackFunction)
    {
        $this->renderJSCallback = $callbackFunction;
    }

    /**
     * If no Javascript is used, this is the command that the user will request
     * on the server when selecting on item from the result set.
     * The itemSelectedJSCallback takes precedence over this command.
     * @param Command $viewCmd Command to call on the server when selecting a result
     * @param string $key The key to set in the value supplied to the command.
     * @param string $attr The attribute to set in $key
     */
    public function setViewCommand(Command $viewCmd, $key, $attr)
    {
        $this->viewCommand = $viewCmd;
        $this->viewCommandKey = $key;
        $this->viewCommandAttr = $attr;
    }

    /**
     * Specifies a Javascript function that should be invoked whenever an
     * item in the result set is selected. Must be a valid Javascript function.
     * This takes precedence over the viewCommand, which can also be optionally set.
     * @param $callback string The name of the callback function
     */
    public function setItemSelectedJSCallback($callbackFunction)
    {
        $this->itemSelectedJSCallback = $callbackFunction;
    }
    
    /**
     * Sets an array of fields which the Javascript will populate with the selected
     * item's "data". Can be hidden fields, other text boxes, etc.
     * @param array $fields
     */
    public function setFieldsSetOnSelect(Array $fields)
    {
        $this->fieldsSetOnSelect = $fields;
    }

    public function show()
    {
        $params = array();
        
        $params['elementId']    = $this->elementId;
        $params['searchDelay']  = (isset($this->searchDelay)) ? $this->searchDelay : AJAX_SEARCH_DELAY_DEFAULT;

        // These must be set, or the JS won't work
        $params['ajaxUri'] = $this->quote($this->queryCommand->getUri());
        $params['searchWhenEmpty'] = $this->searchWhenEmpty;
        $params['renderJSCallback'] = $this->renderJSCallback;
        
        $params['selectedUri']      = $this->quote((isset($this->viewCommand)) ? $this->viewCommand->getURI() : null);
        $params['selectedUriKey']   = $this->quote((isset($this->viewCommandKey)) ? $this->viewCommandKey : null);
        $params['selectedAttr']     = $this->quote((isset($this->viewCommandAttr)) ? $this->viewCommandAttr : null);
        $params['selectedCallback'] = (isset($this->itemSelectedJSCallback)) ? $this->itemSelectedJSCallback : 'null';
        $params['fieldsSetOnSelect']= json_encode($this->fieldsSetOnSelect);

        return javascript('modules/sdr/AjaxSearch', $params);
    }
    
    protected function quote($string)
    {
        if(is_null($string)) {
            return 'null';
        }
        
        return "'$string'";
    }
}

?>
