<?php

namespace sdr;

class NamespaceUtils
{
    private function __construct() { }
    
    public static function namespaceToPath($namespace)
    {
        // Remove leading slash if any
        if(substr($namespace, 0, 1) == '\\') $namespace = substr($namespace, 1);
        
        // Remove SDR\ and turn \ into /
        return preg_replace("|\\\\|", '/', substr($namespace, 4));
    }
    
    public static function pathToNamespace($path)
    {
        // Add sdr\ and turn / into \
        return 'sdr\\' . preg_replace("|/|", '\\', $path);
    }
    
    public static function getClassPath()
    {
        // Not really a namespace thing...
        return PHPWS_SOURCE_DIR . 'mod/sdr/class/';
    }
    
    public static function getAllInNamespace($namespace)
    {
        // Build fully qualified path
        $dir = self::getClassPath() . self::namespaceToPath($namespace);
        
        if(!file_exists($dir))
            // TODO: Exception Cleanup
            throw new Exception("Specified path does not exist: $dir");
        
        $files = scandir("$dir/");
        $classes = array();
        foreach($files as $f) {
            // Look for things that aren't directories and don't start with '.' and do end in .php
            if(!is_dir($dir . '/' . $f) && substr($f, 0, 1) != '.' && substr($f, -4, 4) == '.php') {
                // Format like Namespace\Class
                $classes[] = $namespace . '\\' . preg_replace('/\.php/', '', $f);
            }
        }
        
        return $classes;
    }
}

?>
