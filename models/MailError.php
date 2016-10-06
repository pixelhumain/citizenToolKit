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
    public $personId = "";
    public $description = "";
    public $timestamp = "";

    public function __construct( $params ) {
        $manageableEvent = array(self::EVENT_DROPPED_EMAIL, self::EVENT_SPAM_COMPLAINTS, self::EVENT_BOUNCED_EMAIL);
        $this->event = @$params["event"];
        
        if (empty($this->event) || !in_array($this->event, $manageableEvent)) {
            throw new CTKExeception("Unknown Event in mail error : ".$event);
        }

        //recipient and account
        $this->recipient = @$params["recipient"];
        if (empty($this->recipient)) 
            throw new CTKExeception("No email specified");
        $account = PHDB::findOne(Person::COLLECTION,array("email"=>$this->recipient));
        if (!$account) 
            throw new CTKExeception("unknown user with that email : ".$this->recipient);
        else 
            $personId = (String) $account["_id"];

        $this->reason = @$params["reason"];
        $this->description = @$params["description"];
        $this->timestamp = @$params["timestamp"];
    }

    public function actionOnEvent() {
        if ($event == self::EVENT_DROPPED_EMAIL) {
            //Set invalid email flag on the person
            PHDB::update( Person::COLLECTION, array("_id" => $account["_id"]), array('$set' => array("isNotValidEmail" => true)));
            $this->save();
        }
    }

    public function save() {
        PHDB::insert(self::COLLECTION, array("event" => $this->event, "recipient"=> $this->recipient, "personId" => $this->personId, "reason"=> $this->reason, "description"=> $this->description, "timestamp"=> new MongoDate($this->timestamp)));
    }
}