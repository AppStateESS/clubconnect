<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class TranscriptRequestProcessView extends sdr\View {
    private $transcriptRequest;

    public function __construct(TranscriptRequest $req)
    {
        $this->transcriptRequest = $req;
    }

    public function show()
    {
        PHPWS_Core::requireInc('sdr', 'deliveryMethods.php');

        $req = $this->transcriptRequest;

        PHPWS_Core::initModClass('sdr', 'Member.php');
        $member = new Member($req->getMemberId());

        $tpl = array();
        $tpl['TITLE'] = dgettext('sdr', 'Co-Curricular Transcript Requests');
        $tpl['SUB'] = $member->getFullName();
        $tpl['MENU'] = ''; // TODO: Return to pending requests, basically

        $tpl['SUBMITTED'] = date('d M Y', $req->getSubmissionTimestamp());
	//if the delivery_method is a manual address (or ASU Box) use these lines
	if($req->getDeliveryMethod() == DIFF_ADDRESS || $req->getDeliveryMethod() == ASU_BOX) {
	    $tpl['ADDRESS1'] = $req->getAddress1();
	    $tpl['ADDRESS2'] = $req->getAddress2();
	    $tpl['ADDRESS3'] = $req->getAddress3();
	    $tpl['CITY'] = $req->getCity();
	    $tpl['STATE'] = $req->getState();
	    $tpl['ZIP'] = $req->getZip();
	}
	//csil office
	else if($req->getDeliveryMethod() == CSIL_OFFICE) {
	    $tpl['ADDRESS1'] = "Student will pick up at CSIL Office";
	}
	//prestored banner address
	else {
	  //if its not all numbers (as in the ASU Box addresses) use the regular format
	  if(!preg_match("/^[0-9]*$/",$req->getAddress1())) {
	      $tpl['ADDRESS1'] = $req->getAddress1();
	      $tpl['ADDRESS2'] = $req->getAddress2();
	      $tpl['ADDRESS3'] = $req->getAddress3();
	      $tpl['CITY'] = $req->getCity();
	      $tpl['STATE'] = $req->getState();
	      $tpl['ZIP'] = $req->getZip();
	  }
	  else {
	      //adds ASU Box to the address line to increase readability
	      $tpl['ADDRESS1'] = "ASU Box ".$req->getAddress1();
	      $tpl['ADDRESS2'] = $req->getAddress2();
	      $tpl['ADDRESS3'] = $req->getAddress3();
	      $tpl['CITY'] = $req->getCity();
	      $tpl['STATE'] = $req->getState();
	      $tpl['ZIP'] = $req->getZip();
	  }
	}
	
        PHPWS_Core::initModClass('sdr', 'TranscriptRequestProcessMenu.php');
        $processMenu = new TranscriptRequestProcessMenu($req);
        $tpl['PROCESS_MENU'] = $processMenu->show();

        return PHPWS_Template::process($tpl, 'sdr', 'TranscriptRequestProcessView.tpl');
    }
}

?>
