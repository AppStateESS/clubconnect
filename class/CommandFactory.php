<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'UriMap.php');

class CommandFactory {
    const COMMAND_DIR = 'command';
    const MAPFILE     = 'sdr.urimap';
    const CACHEKEY    = 'SdrUriMap';

    private static $INSTANCE;

    public static function getInstance()
    {
        if(is_null(self::$INSTANCE)) {
            if(function_exists('apc_fetch') && SDRSettings::getApcEnabled()) {
                $map = apc_fetch(self::CACHEKEY.self::MAPFILE);
                if($map === FALSE) unset($map);
            }

            if(!isset($map))
                $map = new UriMap(self::MAPFILE);
            
            self::$INSTANCE = new CommandFactory($map);
        }

        return self::$INSTANCE;
    }

    // API Compatibility
    public static function getCommand($action, array $vars = null)
    {
        return self::getInstance()->get($action, $vars);
    }

    public static function getCommandByUri($uri)
    {
        return self::getInstance()->getByUri(
            CommandContext::getInstance()->trimUri($uri));
    }

    public static function getUriByCommand(Command $command)
    {
        return self::getInstance()->reverseMap($command);
    }

    protected $map;

    private function __construct(UriMap $map)
    {
        $this->map = $map;
    }

    public function get($action, array $vars = null)
    {
        $class = self::staticInit($action);
        
        $cmd = new $class();
        if(!is_null($vars)) $cmd->setParamValues($vars);
        
        $cmd->setLogger(ConfigurationManager::getInstance()->getActivityLog());

        return $cmd;
    }

    public function reverseMap(Command $command)
    {
        $uri = CommandContext::getInstance()->untrimUri($this->map->getUri($command->getAction(), $command->getRequestVars()));
        if(function_exists('apc_store'))
            apc_store(self::CACHEKEY.self::MAPFILE, $this->map);
        return $uri;
    }

    public function getByUri($uri)
    {
        $class = $this->map->getAction($uri);
        if($class === NULL) {
            PHPWS_Core::errorPage(404);
        }
        $cmd = $this->get($class);
        $cmd->setParamValues($this->map->getVars($uri));
        if(function_exists('apc_store'))
            apc_store(self::CACHEKEY.self::MAPFILE, $this->map);
        return $cmd;
    }
    
    public function staticInit($action)
    {
    	$dir = self::COMMAND_DIR;
    	
    	$class = $action;

        if(!file_exists(PHPWS_SOURCE_DIR . "mod/sdr/class/{$dir}/{$class}.php")) {
            // TODO: Eliminate This
            if(substr($class, -7) != 'Command') {
                return self::staticInit($class . 'Command');
            }
            debug_print_backtrace();
    		PHPWS_Core::initModClass('sdr', 'exception/CommandNotFoundException.php');
    		throw new CommandNotFoundException("Could not initialize {$class}");
        }
    	
    	PHPWS_Core::initModClass('sdr', "{$dir}/{$class}.php");
    	
    	return $class;
    }
}

?>
