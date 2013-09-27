<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('sdr', 'exception/SdrPdoException.php');

abstract class PDOController
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = PDOFactory::getInstance();
    }

    protected function safeExecute($stmt, $data = null)
    {
        $result = $stmt->execute($data);

        if(!$result) {
            $e = new SdrPdoException('An error occurred on the server. Please try again later.');
            $e->setErrorInfo($stmt->errorInfo());
            throw $e;
        }

        return $result;
    }

    protected function unPrefix($prefix, $result)
    {
        $ret = array();

        $len = strlen($prefix);

        foreach($result as $key => $val) {
            if(substr($key, 0, $len) == $prefix) {
                $ret[substr($key, $len)] = $val;
            }
        }

        return $ret;
    }

    protected function assocDiff($old, $new, $fields)
    {
        foreach($fields as $field) {
            if($old[$field] != $new[$field]) {
                return true;
            }
        }

        return false;
    }
}

?>
