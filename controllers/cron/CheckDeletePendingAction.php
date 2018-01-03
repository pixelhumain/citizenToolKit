<?php
class CheckDeletePendingAction extends CAction {
	
	//Check if elements need to be deleted when the delay for admins to stop the delete is over
	//If $forceDelete is set to true : all the pending delete organization will be deleted without checking the date
	public function run($forceDelete=false) {
		$where = array("status" => "deletePending");
		$type2check = array(
				Organization::COLLECTION, 
				Project::COLLECTION, 
				Event::COLLECTION
			);

		$debug = array("type2check"=>$type2check );
		foreach ($type2check as $type) 
		{
			$elementList = PHDB::find( $type,$where );
			$debug["count"] = count($elementList);
			if(count($elementList))
			{
				if(!@$debug[$type])
					$debug[$type] = array();

				foreach ($elementList as $id => $element) 
				{
					if ($this->canBeDeleted($element) || $forceDelete) 
					{
						$res = Element::deleteElement($type, $id, @$element["reasonDelete"], @$element["userAskingToDelete"]);
						$res["type"] = $type;
						$res["reasonDelete"] = @$element["reasonDelete"];
						$res["userAskingToDelete"] = @$element["userAskingToDelete"];
						if (!$res["result"]) {
							//error_log("Error deleting the element ".$id."of type ".$type. ". Reason : ".$res["msg"]);
							//Notify the super admins
							Notification::actionToAdmin(
					            ActStr::VERB_DELETE, 
					            array("type" => Cron::COLLECTION), 
					            array( "event" => Element::ERROR_DELETING, 
					            		"reason"=>$res["msg"], 
					            		"id" => $id, 
					            		"type"=>$type),
					            array( "id" => $id, 
					            		"type"=>$type, 
					            		"name" => $element["name"])
					        );
						}
						
						$debug[$type][$id] = $res;
					} else {
						if(!@$debug[$type]["pending"])
							$debug[$type]["pending"] = array();

						$debug[$type]["pending"][$id] = array( 
															"name" => $element["name"],
															"date" => date(DATE_ISO8601,$element["statusDate"]->sec),
															);
					}
				}
			}
			
		}
		Rest::json($debug);
	}

	private function canBeDeleted($element) {
		$now = new DateTime("now");
		$dateToBeDeleted = $element["statusDate"]->toDateTime()->add(new DateInterval('P'.Element::NB_DAY_BEFORE_DELETE.'D'));
		//error_log("check with date :".$dateToBeDeleted->format(DateTime::ISO8601));
		return $now > $dateToBeDeleted;
	}
}