<?php

/**
 * Message - Abstract class which declares methods every Message type must define and provides some default/shared functionality (load/save,send, etc)
 */

abstract class Message {
    
    protected $toUsername;   // The user whom this message is for
    protected $fromUsername; // The user whom this message is from
    protected $subject;   // A short description of the message's subject1
    protected $templateName;    // The name of the template used to create this message
    protected $tags;            // The template tags for this message
    protected $messageType;     // The type of the message, valid values defined above
    
    protected function __construct($toUsername, $fromUsername, $subject, $template, $tags = NULL)
    {
        $this->toUsername       = $toUsername;
        $this->fromUsername     = $fromUsername;
        $this->subject          = $subject;
        $this->templateName     = $template;
        
        // Initialize tags
        if(!is_null($tags)){
            $this->tags = $tags;
        }else{
            $tags = array();
        }
    }
    
    protected function load()
    {
        // TODO
    }
    
    public function save()
    {
        // TODO
    }
    
    public function send()
    {
        $sender = MessageSenderFactory::getSender($this);
        $sender->send();
    }
    
    /**
     * Replaces any previously set tags with the given associative array of tags.
     * @param array $tags - Associative array of tags to set
     */
    public function setTags(Array $tags){
        $this->tags = $tags;
    }
    
    /**
     * Appends an associate array of one or more tags onto the existing array of tags.
     * @param $tags - Associative array of tags to add
     */
    public function addTags(Array $tags){
        $this->setTags(array_merge($this->tags, $tags));
    }
    
    public function getTags(){
        return $this->tags;
    }
    
    public function getTemplateName(){
        return $this->templateName;
    }
    
    public function getSubject(){
        return $this->subject;
    }
}
?>