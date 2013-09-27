<?php

/**
 * TranscriptRequestAddress class - An address to send transcripts to in a request
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class TranscriptRequestAddress {

    public $id;
    public $transcript_request_id;
    public $copies;
    public $address_1;
    public $address_2;
    public $address_3;
    public $city;
    public $state;
    public $zip;

    public function __construct($id = NULL, $tr_id = NULL)
    {
        if(!is_null($id)) {
            $this->initById($id);
        } else if(!is_null($tr_id)) {
            $this->initByTranscriptRequest($tr_id);
        }
    }

    public function initById($id)
    {
        $this->id = $id;

        $db = new PHPWS_DB('sdr_transcript_request_address');
        $db->addWhere('id', $id);

        $this->init($db);
    }

    public function initByTranscriptRequest($tr_id)
    {
        $this->transcript_request_id = $tr_id;

        $db = new PHPWS_DB('sdr_transcript_request_address');
        $db->addWhere('transcript_request_id', $tr_id);

        $this->init($db);
    }

    public function init($id)
    {
        $result = $db->loadObject($this);
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_CorE::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('sdr_transcript_address');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return true;
    }
}

?>
