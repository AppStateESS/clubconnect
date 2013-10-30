<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class CommandContext {
    private static $INSTANCE;

    public static function getInstance()
    {
        if(is_null(self::$INSTANCE)) {
            self::$INSTANCE = new CommandContext();
        }

        return self::$INSTANCE;
    }

    private $params = array();
    private $error = array();
    private $content = "";
    private $successCommand = null;
    private $rewritten = FALSE;
    private $method = "";
    private $uri = "";
    private $preventPush = FALSE;
    private $postdata = null;
    private $dontJson = false;

    private function __construct()
    {
        foreach($_REQUEST as $key => $val) {
            if(!is_null($val)) {
                $this->addParam($key, $val);
            }
        }

        $this->method = $_SERVER['REQUEST_METHOD'];

        $parts = preg_split('/\?/', $_SERVER['REQUEST_URI']);
        $this->uri = $this->trimUri($parts[0]);
        
        if(!isset($_SERVER['REDIRECT_URL'])) $this->rewritten = FALSE;
        else if(empty($_SERVER['QUERY_STRING'])) $this->rewritten = TRUE;
        else $this->rewritten = FALSE;

        // Load JSON Data
        $this->postdata = file_get_contents('php://input');
    }

    public function getJsonData()
    {
        return json_decode($this->postdata, true);
    }

    public function getRawData()
    {
        return $this->postdata;
    }

    public function pleaseDontJson()
    {
        $this->dontJson = true;
    }

    public function shouldJson()
    {
        return !$this->dontJson;
    }

    public function isActiveCommand(Command $cmd)
    {
        // If the action is set, this is easy.
        $action = $this->get('action');
        if($action != null && $action == $cmd->getAction()) return true;

        // Now we compare URIs
        $uri = $this->trimUri(CommandFactory::getUriByCommand($cmd));

        return $uri == $this->getUri();
    }

    public function trimUri($uri)
    {
        $trim = SDRSettings::getBaseURI();
        $length = strlen($trim);
        if(substr($uri, 0, $length) == $trim)
            return substr($uri, $length);

        return $uri;
    }

    public function untrimUri($uri)
    {
        if($uri === FALSE) return $uri;

        return SDRSettings::getBaseURI().$uri;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUri()
    {
        return $this->uri;
    }

    function addParam($key, $val)
    {
        $this->params[$key] = $val;
    }

    function get($key)
    {
        if(!isset($this->params[$key]))
            return NULL;

        return $this->params[$key];
    }

    public function has($key)
    {
        return array_key_exists($key, $this->params);
    }

    function coalesce($key, $default)
    {
        $val = $this->get($key);
        if(is_null($val)) return $default;
        return $val;
    }

    function getParams()
    {
        return $this->params;
    }
    
    function plugObject($obj)
    {
    	return PHPWS_Core::plugObject($obj, $this->params);
    }

    function addError($error)
    {
        $this->error[] = $error;
    }

    function getError()
    {
        return $this->error;
    }

    function setContent($content)
    {
        $this->content = $content;
    }

    function getContent()
    {
        return $this->content;
    }
    
    function isRewritten()
    {
    	return $this->rewritten;
    }

    function preventPushContext()
    {
        $this->preventPush = true;
    }

    function pushContext(Command $command)
    {
        if($this->preventPush) return;

        if(!isset($_SESSION['SDR_Context_Stack'])) {
            $_SESSION['SDR_Context_Stack'] = array();
        }

        array_push($_SESSION['SDR_Context_Stack'], $command->getUri());
    }

    function popContext()
    {
        if(!isset($_SESSION['SDR_Context_Stack'])) return FALSE;

        $uri = array_pop($_SESSION['SDR_Context_Stack']);
        if(is_null($uri)) {
            return FALSE;
        }

        return $uri;
    }

    function errorBack()
    {
        $this->popContextAndRedirect(true);
    }

    function goBack()
    {
        $this->popContextAndRedirect(false);
    }

    function popContextAndRedirect($preventPush)
    {
        $uri = $this->popContext();

        if($preventPush) {
            $_SESSION['SDR_No_Push_Context'] = true;
        }

        if($uri === FALSE) {
            $command = CommandFactory::getCommand('DefaultCommand');
            $command->redirect();
        }

        header('HTTP/1.1 303 See Other');
        header("Location: $uri");
        \sdr\Environment::getInstance()->cleanExit();
    }
}

?>
