<?php
class CheckDeletePendingAction extends CAction {
	
	//Check if elements need to be deleted when the delay for admins to stop the delete is over
	public function run() {
		$where = array("status" => "deletePending");
		$type2check = array(Organization::COLLECTION, Project::COLLECTION, Event::COLLECTION);
		
		foreach ($type2check as $type) {
			$elementList = PHDB::find($type,$where);
			foreach ($elementList as $id => $element) {
				if ($this->canBeDeleted($element)) {
					$res = Element::deleteElement($type, $id, @$element["reasonDelete"], @$element["userAskingToDelete"]);
					if (!$res["result"]) {
						error_log("Error deleting the element ".$id."of type ".$type. ". Reason : ".$res["message"]);
						//
						Notification::actionToAdmin(
				            ActStr::VERB_DELETE, 
				            array("type" => Cron::COLLECTION), 
				            array("event" => Element::ERROR_DELETING, "reason"=>$res["message"]),
				            array("id" => $id, "type"=>$type, "name" => $element["name"])
				        );
					}
				}
			}
		}

	}

	private function canBeDeleted($element) {
		$now = new DateTime("now");
		$dateToBeDeleted = $element["statusDate"]->toDateTime()->add(new DateInterval('P'.Element::NB_DAY_BEFORE_DELETE.'D'));
		return $dateToBeDeleted > $now;
	}
}