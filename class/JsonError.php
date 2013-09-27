<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class JsonError
{
    protected $status;
    protected $message;
    protected $persistent;

    public function __construct($status)
    {
        $this->setStatus($status);
    }

    public function setStatus($status)
    {
        $this->status = "{$_SERVER['SERVER_PROTOCOL']} $status";
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setPersistent($data)
    {
        $this->persistent = $data;
    }

    public function save()
    {
        $ret = array();

        $db = array();

        $db[':status']     = $this->status;
        $db[':message']    = $this->message;
        $db[':persistent'] = json_encode($this->persistent);

        $serverdata = array(
            'SERVER'  => $_SERVER,
            'REQUEST' => $_REQUEST,
            'USER'    => Current_User::getUserObj());
        if(array_key_exists('SDR_Last_Context', $_SESSION)) {
            $serverdata['CONTEXT'] = $_SESSION['SDR_Last_Context'];
        }
        $json = CommandContext::getInstance()->getRawData();
        if(!is_null($json)) {
            $serverdata['RAWPOST'] = $json;
        }

        $db[':server'] = json_encode($serverdata);

        PHPWS_Core::initModClass('sdr', 'PDOFactory.php');
        $pdo = PDOFactory::getInstance();
        if($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $stmt = $pdo->prepare("INSERT INTO sdr_error (id, occurred, status, message, persistent, server) VALUES
            (nextval('sdr_error_seq'), now(), :status, :message, :persistent, :server) RETURNING id");

        if($stmt->execute($db)) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $ret['message'] = $this->message . " This error has been reported with reference ID {$row['id']}";
        } else {
            $ret['message'] = $this->message . ' This error has not been reported due to further problems on the server.';
        }

        header($this->status);
        return $ret;
    }
}

?>
