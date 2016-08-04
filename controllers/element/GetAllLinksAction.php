<?php

class GetAllLinksAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id) { 
    	//$controller=$this->getController();
		$links=$_POST["links"];
		$contextMap = array();
		$contextMap["organization"] = array();
		$contextMap["people"] = array();
		$contextMap["organizations"] = array();
		$contextMap["projects"] = array();
		$contextMap["events"] = array();
		$contextMap["followers"] = array();
		if(!empty($links)){
			if(isset($links["members"])){
				foreach ($links["members"] as $key => $aMember) {
					if($aMember["type"]==Organization::COLLECTION){
						$newOrga = Organization::getSimpleOrganizationById($key);
						if(!empty($newOrga)){
							if ($aMember["type"] == Organization::COLLECTION && @$aMember["isAdmin"]){
								$newOrga["isAdmin"]=true;  				
							}
							$newOrga["type"]=Organization::COLLECTION;
							if (!@$newOrga["disabled"]) {
								array_push($contextMap["organizations"], $newOrga);
							}
						}
					} 
					else if($aMember["type"]==Person::COLLECTION){
						$newCitoyen = Person::getSimpleUserById($key);
						if (!empty($newCitoyen)) {
							if (@$aMember["type"] == Person::COLLECTION) {
								if(@$aMember["isAdmin"]){
									if(@$aMember["isAdminPending"])
										$newCitoyen["isAdminPending"]=true;  
										$newCitoyen["isAdmin"]=true;  	
								}			
								if(@$aMember["toBeValidated"]){
									$newCitoyen["toBeValidated"]=true;  
								}		
			  				
							}
							$newCitoyen["type"]=Person::COLLECTION;
							array_push($contextMap["people"], $newCitoyen);
						}
					}
				}
			}
			// Link with events
			if(isset($links["events"])){
				foreach ($links["events"] as $keyEv => $valueEv) {
					 $event = Event::getSimpleEventById($keyEv);
					 if(!empty($event))
					 	array_push($contextMap["events"], $event);
				}
			}
	
			// Link with projects
			if(isset($links["projects"])){
				foreach ($links["projects"] as $keyProj => $valueProj) {
					 $project = Project::getSimpleProjectById($keyProj);
					 if (!empty($project))
	           		 array_push($contextMap["projects"], $project);
				}
			}

			if(isset($links["followers"])){
				foreach ($links["followers"] as $key => $value) {
					$newCitoyen = Person::getSimpleUserById($key);
					if (!empty($newCitoyen))
					array_push($contextMap["followers"], $newCitoyen);
				}
			}


			// Link with needs
			/*if(isset($organization["links"]["needs"])){
				foreach ($organization["links"]["needs"] as $key => $value){
					$need = Need::getSimpleNeedById($key);
					//array_push($contextMap["projects"], $project);
				}
			}*/
		}	
		return Rest::json($contextMap);
		Yii::app()->end();
	}
}

?>