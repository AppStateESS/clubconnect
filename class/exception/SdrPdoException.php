<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class SdrPdoException extends \Exception
{
    protected $errorInfo;

    public function setErrorInfo($info)
    {
        $this->errorInfo = $info;
    }

    public function getErrorInfo()
    {
        return $this->errorInfo;
    }
}

?>
