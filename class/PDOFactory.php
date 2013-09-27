<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class PDOFactory
{
    protected static $INSTANCE;

    public static function getInstance()
    {
        if(!self::$INSTANCE) {
            $matches = array();
            preg_match('/([a-zA-Z0-9]+):\/\/([a-zA-Z0-9]+):([a-zA-Z0-9]+)@([a-zA-Z0-9.]+)\/([a-z0-9]+)/',
                PHPWS_DSN, $matches);
            self::$INSTANCE = new PDO("pgsql:host={$matches[4]};dbname={$matches[5]};user={$matches[2]};password={$matches[3]}");
        }

        return self::$INSTANCE;
    }
}

?>
