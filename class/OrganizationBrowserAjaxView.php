<?php

/**
 * SDR Organization Browser Category View
 * Provides jQuery business for the By-Category view.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class OrganizationBrowserAjaxView extends sdr\View
{
	private $viewCommand;
	private $jsCategoryCallback;
	private $jsOrganizationCallback;
	private $extra;
	private $alternate;

	/**
	 * Call this method only if control is to be returned to the server when
	 * an organization is successfully selected in jQuery land.
	 *
	 * @param $vars array An array of arrays, that looks like this:
	 *      array(
	 *          array('PARAM'=>'Parameter1', 'VALUE'=>'Value1'),
	 *          array('PARAM'=>'Parameter2', 'VALUE'=>'Value2'), ... );
	 */
	public function setViewCommand(Command $cmd)
	{
		$this->viewCommand = $cmd;
	}

	/**
	 * Specifies a Javascript function that should be invoked whenever a
	 * category is successfully selected in jQuery land.
	 * @param $callback string The name of the callback function
	 */
	public function setJsCategoryCallback($callback)
	{
		$this->jsCategoryCallback = $callback;
	}

	/**
	 * Specifies a Javascript function that should be invoked whenever an
	 * organization is successfully selected in jQuery land.
	 * @param $callback string The name of the callback function
	 */
	public function setJsOrganizationCallback($callback)
	{
		$this->jsOrganizationCallback = $callback;
	}

	/**
	 * Use this to display something below the Organization Browser.  Usually,
	 * either a OrganizationProfile or a OrganizationManager goes here.
	 */
	public function setExtra($extra)
	{
		$this->extra = $extra;
	}

	/**
	 * Provide an "alternate" link to a less-javascripty view
	 */
	public function setAlternate($alt)
	{
		$this->alternate = $alt;
	}

	public function show()
	{
		$vars = array();

		if(isset($this->viewCommand)) {
			foreach($this->viewCommand->getRequestVars() as $key=>$val) {
				$p = array();
				$p['PARAM'] = $key;
				$p['VALUE'] = $val;
				$vars['PARAMS'][] = $p;
			}
		}

		if(isset($this->jsCategoryCallback)) {
			$vars['CAT_SEL_CALLBACK'] = $this->jsCategoryCallback;
		}

		if(isset($this->jsOrganizationCallback)) {
			$vars['ORG_SEL_CALLBACK'] = $this->jsOrganizationCallback;
		}

		if(isset($this->extra)) {
			$vars['EXTRA'] = $this->extra;
		}

		if(isset($this->alternate)) {
			$vars['ALTERNATE'] = $this->alternate;
		}

		return javascript('modules/sdr/OrganizationBrowserCategory', $vars);
	}
}

?>
