<?php

/**
 * Shows a JQuery popup with the given command as the contents
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class JQueryDialogCommand extends Command {
    
    private static $jsIncluded = false;
    
    private $viewCommand;
    private $dialogTitle;
    
    public function setViewCommand(Command $cmd){
        $this->viewCommand = $cmd;
    }
    
    public function setDialogTitle($title){
        $this->dialogTitle = $title;
    }
    
    public function getRequestVars(){
        $vars = $this->viewCommand->getRequestVars();
        
        return $vars;
    }
    
    private function init(){
        if(!self::$jsIncluded){
            javascript('modules/sdr/JQueryDialog');
            self::$jsIncluded = true;
        }
    }
    
    public function getLink($text = NULL, $target = NULL, $cssClass = NULL, $title = NULL)
    {
        self::init();
        
        $vars = $this->getRequestVars();
        
        $link = new PHPWS_Link(dgettext('sdr', $text), 'sdr', $vars, true);
        $link->setOnClick('openJQueryDialog(\'' . $this->getURI() . '&ajax=true\', \'' . $this->dialogTitle . '\'); return false;');
        
        return $link->get();
    }
    
    public function execute(CommandContext $context){
        
    }
}

?>
