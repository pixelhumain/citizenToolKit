<?php
/**
 * disconnect 2 people together 
 */
class RemoveAttendeeAction extends CTKAction {
    
    public function run($id,$type, $attendeeId) {
		
		if (! $this->userLogguedAndValid()) {
        	return Rest::json(array("result" => false, "msg" => "The current user is not valid : please login."));
        }
		$ownerLink=Link::person2events;
		$targetLink= Link::event2person;
        return Rest::json(Link::disconnectPerson($attendeeId, Person::COLLECTION, $id, $type, $ownerLink, $targetLink));
    }
}