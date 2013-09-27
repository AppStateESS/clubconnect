<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 *
 * Uses code from sec.class.inc.php by Mark Davidson.
 */

class MimeEmail {
    protected $recipients;
    protected $sender;
    protected $subject;
    protected $body;
    protected $attachments;
    protected $charset = 'ISO-8859-1';
    protected $debugMode = false;
    protected $boundary;

    public function __construct()
    {
        $this->recipients = array();
        $this->attachments = array();

        $this->boundary = uniqid(rand());
    }

    public function setDebugMode($debug = true)
    {
        $this->debugMode = $debug;
    }

    // From code by Mark Davidson
    public function addressValid($email)
    {
        return preg_match('/^[A-Z0-9._%-]+@(?:[A-Z0-9-]+\\.)+[A-Z]{2,4}$/i', $email);
    }

    public function addRecipientMember(Member $recipient)
    {
        $this->addRecipient($recipient->getFriendlyName(), $recipient->getUsername() . '@appstate.edu');
    }

    public function addRecipient($name, $email = null)
    {
        if(is_null($email))
            $this->recipients[] = $name;
        else
            $this->recipients[] = "$name <$email>";
    }

    public function setSender($email)
    {
        $this->sender = $email;
    }

    public function setSenderMember(Member $member)
    {
        $this->sender = $member->getFriendlyName() . ' <' . $member->getUsername() . '@appstate.edu>';
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function addAttachment($file)
    {
        $this->attachments[] = $file;
    }

    public function setBody($body)
    {
        $this->body = $body;
    }

    public function isHtml()
    {
        return !(strip_tags($this->body) == $this->body);
    }

    protected function formatHeader($key, $val)
    {
        return $this->crlf("$key: $val");
    }

    protected function formatAsPlain()
    {
        return strip_tags($this->body);
    }

    protected function formatAsHtml()
    {
        return $this->body;
    }

    protected function buildHeader($recipient)
    {
        $header = $this->formatHeader('To',           $recipient);
        $header.= $this->formatHeader('From',         $this->sender);
        $header.= $this->formatHeader('Subject',      $this->subject);
        $header.= $this->formatHeader('X-Mailer',     'ClubConnect(phpWebSite Platform)');
        $header.= $this->formatHeader('MIME-Version', '1.0');

        if(!empty($this->attachments) || $this->isHtml()) {
            $header.= $this->formatHeader('Content-Type', 'multipart/mixed; boundary="'.$this->boundary.'"');
        }

        return $header;
    }

    protected function crlf($string = '')
    {
        return "$string\r\n";
    }

    protected function buildMessage($recipient)
    {
        if(!empty($this->attachments) || $this->isHtml()) {
            $message = $this->crlf('This is a multi-part message in MIME format.');
            $message.= $this->crlf("--{$this->boundary}");

            if($this->isHtml()) {
                $htmlboundary = uniqid(rand());
                $message.= $this->crlf('Content-Type: multipart/alternative;');
                $message.= $this->crlf("\tboundary=\"{$htmlboundary}\"");
                $message.= $this->crlf();
                $message.= $this->crlf('This is a multi-part message in MIME format.');
                $message.= $this->crlf("--{$htmlboundary}");
                $message.= $this->crlf("Content-Type: text/plain; charset={$this->charset}; format=flowed");
                $message.= $this->crlf("Content-Transfer-Encoding: 7bit");
                $message.= $this->crlf();
                $message.= $this->crlf($this->formatAsPlain());
                $message.= $this->crlf();
                $message.= $this->crlf("--{$htmlboundary}");
                $message.= $this->crlf("Content-Type: text/html; charset={$this->charset};");
                $message.= $this->crlf("Content-Transfer-Encoding: 7bit");
                $message.= $this->crlf($this->formatAsHtml());
                $message.= $this->crlf("--{$htmlboundary}--");
                $message.= $this->crlf();
            }

            foreach($this->attachments as $attach) {
                $basename = basename($file);
                $message.= $this->crlf("--{$this->boundary}");
                $message.= $this->crlf("Content-Type: ".$this->mime_type($file)."; name=\"{$basename}\"");
                $message.= $this->crlf("Content-Transfer-Encoding: base64");
                $message.= $this->crlf("Content-Disposition: attachment; filename=\"{$basename}\"");
                $message.= $this->crlf();
                $message.= chunk_split(base64_encode(fread(fopen($file,'rb'),filesize($file))),72) . "\r\n";
            }

            $message.= $this->crlf("--{$this->boundary}--");

            return $message;
        } else {
            return $this->body;
        }
    }

    public function send()
    {
        foreach($this->recipients as $recipient)
        {
            $head = $this->buildHeader($recipient);
            $msg = $this->buildMessage($recipient);

            if($this->debugMode) {
                var_dump(array('Recipient' => $recipient
                              ,'Subject'   => $this->subject
                              ,'Header'    => $head
                              ,'Message'   => $msg
                              ));
            } else {
                mail($recipient, $this->subject, $msg, $head);
            }
        }
    }
}

?>
