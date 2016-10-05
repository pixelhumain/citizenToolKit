<?php
/*
Contains a mailError hooked by mailgun
 */
class MailError {
    const COLLECTION = "mailerror";
    
    const EVENT_DROPPED_EMAIL = "dropped";
    const EVENT_SPAM_COMPLAINTS = "complained";
    const EVENT_BOUNCED_EMAIL = "bounced";

    public $event = "";
    public $recipient = "";
    public $reason = "";
    public $description = "";
    public $timestamp = "";

    public function __construct( $params ) {
        $manageableEvent = array(self::EVENT_DROPPED_EMAIL, self::EVENT_SPAM_COMPLAINTS, self::EVENT_BOUNCED_EMAIL);
        $this->event = @$params["event"];
        
        if (empty($this->event) || !in_array($this->event, $manageableEvent)) {
            throw new CTKExeception("Unknown Event in in mail error : ".$event);
        }

        $this->recipient = @$params["recipient"];
        $this->reason = @$params["reason"];
        $this->description = @$params["description"];
        $this->timestamp = @$params["timestamp"];
    }

    public function save() {
        PHDB::insert(_self::COLLECTION, array("event" => $this->event, "recipient"=> $this->recipient, "reason"=> $this->reason, "description"=> $this->description, "timestamp"=> new MongoDate($this->timestamp)));
    }
}