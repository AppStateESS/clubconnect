<?php

/**
 * TranscriptRequest class - Represents a reqeust for official transcript copies
 * @author Jeremy Booker
 */

class TranscriptRequest {

    public $id;
    public $member_id;
    public $email;
    public $address_1;
    public $address_2;
    public $address_3;
    public $city;
    public $state;
    public $zip;
    public $submission_timestamp;
    public $processed;

    /** can specify
     * address     : -1
     * CSIL office : -3
     * ASU Box     : -2
    **/
    public $delivery_method;

    public function __construct($id = NULL)
    {
        if(isset($id)){
            $this->id = $id;
            $this->init();
        }
    }

    public function init(){
        $db = new PHPWS_DB('sdr_transcript_request');
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return true;
    }

    public function save()
    {
        $db = new PHPWS_DB('sdr_transcript_request');
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return true;
    }
    
    /**
     *
     * @return Array - Array of tags for use/display by clients (row pagers, templates, etc)
     */
    public function getTags()
    {
        $tags = array();
        $tags['MEMBER_ID']            = $this->getMemberId();
        $tags['STUDENT_ADDRESS_1']    = $this->getAddress1();
        $tags['STUDENT_ADDRESS_2']    = $this->getAddress2();
        $tags['CITY']                 = $this->getCity();
        $tags['STATE']                = $this->getState();
        $tags['ZIP']                  = $this->getZip();
	
	//add a tag here for delivery method
	$tags['DELIVERY_METHOD']     = $this->getDeliveryMethod();
        return $tags;
    }

    public function processTranscriptsPagerTags(Command $viewCommand, $showProcessed)
    {
        $tags = array();

        PHPWS_Core::initModClass('sdr', 'Member.php');
        $member = new Member($this->getMemberId());
        $tags['NAME'] = $member->getLastNameFirst();
        $tags['DATE'] = date('m/d/Y', $this->submission_timestamp);
        if($showProcessed)
            $tags['STATUS'] = $this->processed ? dgettext('sdr', 'Processed') : dgettext('sdr', 'Pending');
        $tags['STATUS_CLASS'] = $this->processed ? 'processed' : 'pending';

        $viewCommand->setTranscriptRequestId($this->getId());

        $tags['ACTIONS'] = $viewCommand->getLink('View');

        return $tags;
    }

    /**
     * Accessor & Mutators
     */
    
    public function getId(){
        return $this->id;
    }

    public function setId($id){
        $this->id = $id;
    }

    public function getMemberId(){
        return $this->member_id;
    }

    public function setMemberId($member_id){
        $this->member_id = $member_id;
    }

    public function getEmail(){
        return $this->email;
    }

    public function setEmail($email){
        $this->email = $email;
    }

    public function getAddress1(){
        return $this->address_1;
    }

    public function setAddress1($address_1){
        $this->address_1 = $address_1;
    }

    public function getAddress2(){
        if(empty($this->address_2)) return null;
        return $this->address_2;
    }

    public function setAddress2($address_2){
        $this->address_2 = $address_2;
    }
    
    public function getAddress3() {
        if(empty($this->address_3)) return null;
    	return $this->address_3;
    }
    
    public function setAddress3($address_3) {
    	$this->address_3 = $address_3;
    }

    public function getCity(){
        return $this->city;
    }

    public function setCity($city){
        $this->city = $city;
    }

    public function getState(){
        return $this->state;
    }

    public function setState($state){
        $this->state = $state;
    }

    public function getZip(){
        return $this->zip;
    }

    public function setZip($zip){
        $this->zip = $zip;
    }

    public function getSubmissionTimestamp(){
        return $this->submission_timestamp;
    }

    public function setSubmissionTimestamp($submission_timestamp){
        $this->submission_timestamp = $submission_timestamp;
    }

    public function getProcessed(){
        return $this->processed;
    }

    public function setProcessed($processed){
        $this->processed = $processed;
    }

    public function getDeliveryMethod(){
        return $this->delivery_method;
    }

    public function setDeliveryMethod($delivery_method){
        $this->delivery_method = $delivery_method;
    }
    
    public static function countPending()
    {
    	$db = new PHPWS_DB('sdr_transcript_request');
    	$db->addWhere('processed', 0);
    	$count = $db->count();
	
    	if(PHPWS_Error::logIfError($count)) {
    		PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
    		throw new DatabaseException('Could not count Pending Transcript Requests');
    	}
    	
    	return $count;
    }
    
    public static function getPending()
    {
    	$db = new PHPWS_DB('sdr_transcript_request');
    	$db->addWhere('processed', 0);
    	$result = $db->getObjects('TranscriptRequest');
    	
    	if(PHPWS_Error::logIfError($result)) {
    		PHPWS_Core::initModClass('sdr', 'exception/DatabaseException.php');
    		throw new DatabaseException('Could not select Pending Transcript Requests');
    	}
    	
    	return $result;
    }
}
?>
