<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class UriMap
{
    protected $mapfile;
    protected $mapping;
    protected $classCache;
    protected $varCache;

    const URI_DELIM = '/';
    const TPL_LEFT = '{';
    const TPL_RIGHT = '}';
    const TPL_CONSUME = '*';

    public function __construct($mapfile)
    {
        $this->mapping = array();

        $this->loadMap($mapfile);
    }

    public function loadMap($mapfile)
    {
        $this->mapfile = PHPWS_SOURCE_DIR . 'mod/sdr/' . $mapfile;

        $fp = fopen($this->mapfile, 'r');
        if($fp === FALSE) {
            throw new Exception('TODO: Make this better');
        }

        while(!feof($fp)) {
            // Read Line, ignore comments
            $line = trim(fgets($fp));
            if(empty($line) || substr($line, 0, 1) == '#') continue;

            // Split into URI, Controller
            $parts = preg_split('/ +/', $line);
            if(count($parts) != 2) {
                throw new Exception('TODO: Make this better');
            }
            list($key, $val) = $parts;

            // Check for Duplicate Patterns
            if(array_key_exists($key, $this->mapping)) {
                throw new Exception('TODO: Make this better');
            }

            $this->mapping[$key] = $val;
        }

        fclose($fp);
    }

    public function getAction($uri)
    {
        if(!isset($this->classCache[$uri])) {
            if(!$this->hasMatch($uri)) {
                return null;
            }
        }

        return $this->classCache[$uri];
    }

    public function getVars($uri)
    {
        if(!isset($this->varCache[$uri])) {
            if(!$this->hasMatch($uri)) {
                return array();
                return null;
            }
        }

        return $this->varCache[$uri];
    }

    public function getUri($class, $values)
    {
        if(!in_array($class, $this->mapping)) {
            return FALSE;
        }

        $keys = array_keys($this->mapping, $class);

        foreach($keys as $key) {
            $result = $this->assembleUri($key, $values);
            if($result === FALSE) continue;

            return $result;
        }

        return FALSE;
    }

    public function assembleUri($tpl, $vals)
    {
        foreach($vals as $key => $val) {
            if($key == 'action') continue;
            $tag = self::TPL_LEFT . $key . self::TPL_RIGHT;
            $count = 0;

            $tpl = str_replace($tag, $val, $tpl, $count);
            if($count < 1) return FALSE;
        }

        return $tpl;
    }

    protected function hasMatch($uri)
    {
        foreach($this->mapping as $tpl => $class) {
            $vars = $this->match($tpl, $uri);
            if($vars !== FALSE) {
                $this->classCache[$uri] = $class;
                $this->varCache[$uri] = $vars;
                return TRUE;
            }
        }

        return FALSE;
    }

    protected function match($tpl, $uri)
    {
        $tl = strlen($tpl);
        $ul = strlen($uri);
        $ti = $ui = 0;

        $vars = array();

        while($ti < $tl && $ui < $ul) {
            // If the characters are equal, so far so good
            if($tpl[$ti] == $uri[$ui]) {
                $ti++; $ui++; continue;
            }
            
            // Template Tag Found
            if($tpl[$ti] == self::TPL_LEFT) {
                $tag = '';
                for($i = $ti + 1; $tpl[$i] != self::TPL_RIGHT; $i++) {
                    $tag .= $tpl[$i];
                    // If we ran off the end with no close tag, that's bad
                    if($i + 1 >= $tl) 
                        throw new MalformedUriTemplateException($tpl);
                }

                // If we're at the end of $tpl...
                if($i + 1 >= $tl) {
                    // If there's no delimiter in the URI before the end, we're good
                    if(strpos($uri, self::URI_DELIM, $ui) === FALSE) {
                        $vars[$tag] = substr($uri, $ui);
                        return $vars;
                    }
                    // Otherwise, no match
                    return FALSE;
                }

                if($tpl[$i + 1] == self::TPL_CONSUME) {
                    // If it's a consume all tag, we're done (be careful with these)
                    $vars[$tag] = substr($uri, $ui);
                    return $vars;
                }

                // Find the character in the uri that happens right after the
                // end tag in $tpl; what is between $ui and that is the value
                // of the var.
                $pos = strpos($uri, $tpl[$i+1], $ui);

                // If we couldn't find that character, no match
                if($pos === FALSE) return FALSE;

                // If we're here, we have a key and a value, and so far so good.
                $vars[$tag] = substr($uri, $ui, $pos - $ui);
                $ti = $i + 1;
                $ui = $pos;
                continue;
            }

            // They're not equal and it's not a template tag.  No match.
            return FALSE;
        }

        // Neither string should have any characters left over.
        if($ti >= $tl && $ui >= $ul) {
            return $vars;
        }

        // Actually, if the URI has a trailing slash, we should redirect to a URI without a trailing slash.
        if($ui + 1 == $ul && $ti >= $tl && $uri[$ui] == self::URI_DELIM) {
            $path = CommandContext::untrimUri(substr($uri, 0, -1));

            header('HTTP/1.1 301 Moved Permanently');
            header("Location: $path");
            SDR::quit();
        }

        // If they do, no match
        return FALSE;
    }
}

?>
