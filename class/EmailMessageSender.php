<?php

PHPWS_Core::initModClass('sdr', 'MessageSender.php');

class EmailMessageSender extends MessageSender{

	function __construct(Message $message)
	{
		parent::__construct($message);
	}

	function send()
	{
		$content = PHPWS_Template::process($this->message->getTags(), 'sdr', $this->message->getTemplateName());

        // Wrap with standard SDR message
        if($this->message->wrap())
            $content = PHPWS_Template::process(array('MESSAGE' => $content), 'sdr', 'email/message.tpl');
		
		// Our template system was intended for HTML and as such doesn't handle
		// blank lines very well.  Here's a hack.  Use [[BLANK]] on any blank line.
		$content = preg_replace('/\[\[BLANK\]\]/', "", $content);

		$this->sendEmail($this->message->getToList(),
            $this->message->getFromAddress(),
		    $this->message->getSubject(),
		    $content,
		    $this->message->getCcList(),
		    $this->message->getBccList());
	}

	protected function sendEmail($to, $from, $subject, $content, $cc = NULL, $bcc = NULL)
	{
		# Sanity checking
		if(!isset($to) || is_null($to)) {
			return false;
		}

		if(!isset($from) || is_null($from)) {
			$from = 'ClubConnect <noreply@tux.appstate.edu>';
		}

		if(!isset($subject) || is_null($subject)) {
			return false;
		}

		if(!isset($content) || is_null($content)) {
			return false;
		}

		# Create a Mail object and set it up
		PHPWS_Core::initCoreClass('Mail.php');
		$message = new PHPWS_Mail;

		$message->setFrom($from);
		$message->setSubject('[ClubConnect] ' . $subject);
		$message->setMessageBody($content);

		# Send the message
		if(SDRSettings::getEmailTestFlag()) {
			$this->log_email($message);
			$emails = explode(',', SDRSettings::getEmailTestAddress());
			foreach($emails as $email)
			    $message->addSendTo(trim($email));
		} else {
			$message->addSendTo($to);

			if(isset($cc)){
				$message->addCarbonCopy($cc);
			}

			if(isset($bcc)){
				$message->addBlindCopy($bcc);
			}
		}

		$result = $message->send();

		if(PHPWS_Error::logIfError($result)){
			PHPWS_Error::log($result);
			return false;
		}

		return true;
	}

	/**
	 * Logs a PHPWS_Mail object to a text file
	 */
	private function log_email($message)
	{
		// Log the message to a text file
		$fd = fopen(PHPWS_SOURCE_DIR . 'logs/email.log',"a");
		fprintf($fd, "=======================\n");

		foreach($message->send_to as $recipient){
			fprintf($fd, "To: %s\n", $recipient);
		}

		if(isset($message->carbon_copy)){
			foreach($message->carbon_copy as $recipient){
				fprintf($fd, "Cc: %s\n", $recipient);
			}
		}

		if(isset($message->blind_copy)){
			foreach($message->blind_copy as $recipient){
				fprintf($fd, "Bcc: %s\n", $bcc);
			}
		}

		fprintf($fd, "From: %s\n", $message->from_address);
		fprintf($fd, "Subject: %s\n", $message->subject_line);
		fprintf($fd, "Content: \n");
		fprintf($fd, "%s\n\n", $message->message_body);

		fclose($fd);
	}
}

?>
