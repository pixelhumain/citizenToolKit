<?php
/**
 * disconnect 2 people together 
 */
class RemoveAttendeeAction extends CTKAction {
    
    public function run($id=null,$type=null, $attendeeId=null) {
		$res =  array("result" => false, "msg" => Yii::t("common", "Incomplete Request."));
		
		if( !$id && isset($_POST["id"]))
          $id = $_POST["id"];
        if( !$type && isset($_POST["type"]))
          $type = $_POST["type"];
        if( !$attendeeId && isset($_POST["attendeeId"]))
          $attendeeId = $_POST["attendeeId"];
		
		if( isset($id) && isset($attendeeId) && isset($type) )
		{
			if (! $this->userLogguedAndValid()) {
	        	$res =  array("result" => false, "msg" => Yii::t("common", "The current user is not valid : please login."));
	        }
			$ownerLink=Link::person2events;
			$targetLink= Link::event2person;
	        $res = Link::disconnectPerson($attendeeId, Person::COLLECTION, $id, $type, $ownerLink, $targetLink);
	    }
	    return Rest::json($res);
    }
}