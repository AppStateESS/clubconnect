<?php

/**
 * SDR Command Menu
 * 
 * Displays a list of links to commands.
 * 
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

abstract class CommandMenu extends sdr\View
{
  private $context;
  private $commands;
	
	public function __construct()
	{
		$this->commands = array();

        $this->setupCommands();
	}

    protected abstract function setupCommands();
	
	public function addCommandByName($text, $command)
	{
		$this->addCommand($text, CommandFactory::getCommand($command));
	}
	
	public function addCommand($text, Command $command)
	{
		$this->commands[$text] = $command;
	}
    
    public function setContext(CommandContext $context)
    {
        $this->context = $context;
    }

    public function countViewableCommands()
    {
        $count = 0;
        foreach($this->commands as $text => $command) {
            if(!$command->allowView()) continue;
            $count++;
        }

        return $count;
    }
	
    public function plugCommands(array &$tpl)
    {
        foreach($this->commands as $text=>$command) {
            // Skip things we can't see
            if(!$command->allowView()) continue;

            // Get Menu link from Command Class
            $uri = $command->getUri();
            $link = '<a href="'.$uri.'">'.$text.'</a>';
            
            // Get Current Context
            if(!isset($this->context)) {
                $this->context = CommandContext::getInstance();
            }
                
            // Determine if link command is the active command
            if($this->context->isActiveCommand($command)) {
                // Add active link
                $tpl['LINK'][]['ACTIVE_LINK'] = $link;
                continue;
            }
                
            // Add inactive link
            $tpl['LINK'][]['LINK'] = $link;
        }
    }
	
    public function show()
    {
      $tpl = array();

      $this->plugCommands($tpl);

      return PHPWS_Template::process($tpl, 'sdr', 'CommandMenu.tpl');
    }
}
