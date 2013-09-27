<?php

/**
 * Command class for submitting the user official transcript request form.
 * @author Jeremy Booker
 */

PHPWS_Core::initModClass('sdr', 'Command.php');

class TranscriptRequestSubmitCommand extends Command {

    function getRequestVars()
    {
        $vars = array('action'=>'TranscriptRequestSubmit');

        return $vars;
    }

    function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('sdr', 'Member.php');
        PHPWS_Core::initModClass('sdr', 'TranscriptRequest.php');

        PHPWS_Core::requireInc('sdr', 'deliveryMethods.php');

        // Data sanity checking
        $err_cmd = CommandFactory::getCommand('ShowUserTranscriptRequest'); // Command to use in case of errors
        
        if(is_null($context->get('delivery_method'))) {
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Please select an address, or enter one manually.');
        }
        
        // TODO: regular expression for valid email
        if(trim($context->get('email')) == '') {
            NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Please enter a valid email address.');
        }

        if(!NQ::isEmpty('sdr')) {
            $err_cmd->redirect();
        }
        
        //check to see what delivery method was chosen here

        if($context->get('delivery_method') == $DIFF_ADDRESS) {
        	// Check manually entered address
        	if(trim($context->get('address_1')) == '')
        	    NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Please enter your street address.');
        	
        	if(trim($context->get('city')) == '')
        	    NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Please enter a city.');
        	    
        	if(trim($context->get('state')) == '')
        	    NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Please select a state.');
        	    
        	if(trim($context->get('zip')) == '')
        	    NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'Please enter a zip code.');
        }

        if(!NQ::isEmpty('sdr')) {
            $err_cmd->redirect();
        }
        
        $request = new TranscriptRequest();
        $member = new Member(NULL, UserStatus::getUsername());
        $request->setMemberId($member->getId());

        //this should use whatever the user enters as the email
        $request->setEmail($context->get('email'));

        if($context->get('delivery_method') == CSIL_OFFICE) {
            $request->setDeliveryMethod($context->get('delivery_method'));
            //set to blanks just to avoid db errors
            $request->setAddress1("CSIL Office");
            $request->setAddress2("");
            $request->setAddress3("");
            $request->setCity("");
            $request->setState("");
            $request->setZip("");
        }

        else if($context->get('delivery_method') == ASU_BOX) {
            //create new Student object
            //get their Address
            $request->setDeliveryMethod($context->get('delivery_method'));
            $student_id = $request->getMemberId();
            $student = new Student($student_id);

            $asubox = $student->getAddresses(array('AB'));
            $request->setAddress1("ASU Box ".$asubox[0]->getLineOne());
            $request->setAddress2("");
            $request->setAddress3("");
            $request->setCity($asubox[0]->getCity());
            $request->setState($asubox[0]->getState());
            $request->setZip($asubox[0]->getZipcode());
        }
        else if($context->get('delivery_method') == DIFF_ADDRESS) {
            // Manual address entered
            $request->setDeliveryMethod($context->get('delivery_method'));
            $request->setAddress1($context->get('address_1'));
            $request->setAddress2($context->get('address_2'));
            $request->setAddress3($context->get('address_3'));
            $request->setCity($context->get('city'));
            $request->setState($context->get('state'));
            $request->setZip($context->get('zip'));
        } else {
            // Banner address selected
            $request->setDeliveryMethod($context->get('delivery_method'));
            PHPWS_Core::initModClass('sdr', 'Address.php');
	    
            $address = new Address($context->get('delivery_method'));
            
            // Make sure the address belongs to this user
            if($address->getStudentId() != $member->getId()) {
            	NQ::Simple('sdr', SDR_NOTIFICATION_ERROR, 'You do not have access to the selected address.');
            }
            
            $request->setAddress1($address->getLineOne());
            $request->setAddress2($address->getLineTwo());
            $request->setAddress3($address->getLineThree());
            $request->setCity($address->getCity());
            $request->setState($address->getState());
            $request->setZip($address->getZipcode());
        }
        if(!NQ::isEmpty('sdr')) {
            $err_cmd->redirect();
        }
        $request->setSubmissionTimestamp(time());
        $request->setProcessed(false);

        $result = $request->save();

        NQ::Simple('sdr', SDR_NOTIFICATION_SUCCESS, 'Your request for an official co-curricular transcript has been submitted.');
        
        // Send an email to the SDR admin informing them about the new request
        PHPWS_Core::initModClass('sdr', 'EmailMessage.php');
        $admin_email = SDRSettings::getTranscriptEmail();
        $email = new EmailMessage($admin_email,'sdr_system', $admin_email, NULL, NULL, NULL, 'Official Transcript Request','email/admin/transcriptRequestNotification.tpl');

        $email_tags = $request->getTags();

        //if ASU Box, append "ASU Box"
        if(preg_match("/^[0-9]*$/",$email_tags['STUDENT_ADDRESS_1'])) {
            $email_tags['STUDENT_ADDRESS_1'] = "ASU Box ".$email_tags['STUDENT_ADDRESS_1'];
        }

        $email_tags['EMAIL'] = $request->getEmail();
        $email_tags['NAME'] = $member->getFullName();

        $email->setTags($email_tags);
        
        $email->send();

        //send student confirmation email
        $student_email = $request->getEmail();
        $s_email = new EmailMessage($student_email,'sdr_system', $student_email, NULL, NULL, NULL, 'Official Transcript Request','email/student/transcriptRequestNotification.tpl');
        $email_tags = $request->getTags();

        //if ASU Box, append "ASU Box"
        if(preg_match("/^[0-9]*$/",$email_tags['STUDENT_ADDRESS_1'])) {
            $email_tags['STUDENT_ADDRESS_1'] = "ASU Box ".$email_tags['STUDENT_ADDRESS_1'];
        }

        $s_email->setTags($email_tags);
        $s_email->send();
        
        PHPWS_Core::initModClass('sdr', 'command/ShowUserTranscriptCommand.php');
        $cmd = CommandFactory::getCommand('ShowUserTranscript');
        $cmd->redirect();
    }
}

?>
