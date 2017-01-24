<?php
/*
Author : SBAR - sylvain.barbot@gmail.com
Contains a mailError hooked by mailgun
 */
class MailError {
    const COLLECTION = "mailerror";
    
    const EVENT_DROPPED_EMAIL = "dropped";
    const EVENT_SPAM_COMPLAINTS = "complained";
    const EVENT_BOUNCED_EMAIL = "bounced";

    //DB field
    public $id = "";
    public $event = "";
    public $recipient = "";
    public $reason = "";
    public $personId = "";
    public $description = "";
    public $timestamp = "";

    public function __construct( $params, $from = "hook" ) {
        //Check the event
        $manageableEvent = array(self::EVENT_DROPPED_EMAIL, self::EVENT_SPAM_COMPLAINTS, self::EVENT_BOUNCED_EMAIL);
        $this->event = @$params["event"];
        
        if (empty($this->event) || !in_array($this->event, $manageableEvent)) {
            throw new CTKException("Unknown Event in mail error : ".$event);
        }

        //Construct from mongo : get the id and transform the mongoDate
        if ($from == "mongo") {
            if (@$params["id"]) $this->id = $params["id"];
            if (@$params["_id"]) $this->id = (String) $params["_id"];
            $this->timestamp = @$params["timestamp"]->sec;
            $this->reason = @$params["reason"];
            $this->description = @$params["description"];   
        } else if ($from == "hook") {
            $this->timestamp = @$params["timestamp"];
            if ($this->event == self::EVENT_DROPPED_EMAIL ) {
                $this->reason = @$params["reason"];
                $this->description = @$params["description"];    
            } else if ($this->event == self::EVENT_BOUNCED_EMAIL ) {
                $this->reason = @$params["code"];
                $this->description = @$params["error"];    
            } else if ($this->event == self::EVENT_SPAM_COMPLAINTS ) {
                $this->reason = "user complained";
                $this->description = "user click on spam complain";    
            }
        }

        //recipient and account
        $this->recipient = @$params["recipient"];
        if (empty($this->recipient)) 
            throw new CTKException("No email specified");
        $account = PHDB::findOne(Person::COLLECTION,array("email"=>$this->recipient));
        if (!$account) 
            throw new CTKException("unknown user with that email : ".$this->recipient);
        else 
            $this->personId = (String) $account["_id"];
    }

    public function actionOnEvent() {
        //Spam ou drop : suspension account
        if ($this->event == self::EVENT_DROPPED_EMAIL || $this->event == self::EVENT_SPAM_COMPLAINTS) {
            //Set invalid email flag on the person
            PHDB::update( Person::COLLECTION, array("_id" => $this->personId), array('$set' => array("isNotValidEmail" => true)));
        //Hard bounce => TODO : try to delete the account
        } else if ($this->event == self::EVENT_BOUNCED_EMAIL) {
            PHDB::update( Person::COLLECTION, array("_id" => $this->personId), array('$set' => array("isNotValidEmail" => true, "hardbounced" => true)));
        }
        $this->save();
        
        //add a Notification to Super Admins
        Notification::actionToAdmin(
            ActStr::VERB_RETURN, 
            array("type" => Cron::COLLECTION), 
            array("id" => $this->id, "type"=>self::COLLECTION, "event" => $this->event),
            array("id" => $this->personId, "type"=>Person::COLLECTION, "email"=>$this->recipient)
        );

    }

    public function save() {
        $mailError = array("event" => $this->event, "recipient"=> $this->recipient, "personId" => $this->personId, "reason"=> $this->reason, "description"=> $this->description, "timestamp"=> new MongoDate($this->timestamp));

        if (PHDB::insert(self::COLLECTION, $mailError)) {
            $this->id = $mailError["_id"];
        } else {
            throw new CTKException("Error inserting a MailError ! ");
        }
    }

    public static function getMailErrorSince($sinceTS) {
        $mailErrors = array();
        $dbMailError = PHDB::findAndSort(self::COLLECTION, array("timestamp" => array('$gt' => new MongoDate($sinceTS))), array("timestamp" => -1));

        foreach ($dbMailError as $mailErrorId => $aMailError) {
            $mailError = new MailError($aMailError, "mongo");
            $mailErrors[$mailErrorId] = $mailError;
        }

        return $mailErrors;
    }
}