<?php

class DetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id) { 
    	$controller=$this->getController();
		$members=array();
		$list = Lists::get(array("eventTypes"));
		if($type == Organization::COLLECTION){
			$element = Organization::getById($id);
			$params["listTypes"] = isset($lists["organisationTypes"]) ? $lists["organisationTypes"] : null;
			$params["eventTypes"] = $list["eventTypes"];
			$params["public"] 			 = isset($lists["public"]) 			  ? $lists["public"] : null;
			$params["typeIntervention"]  = isset($lists["typeIntervention"])  ? $lists["typeIntervention"] : null;
			$params["NGOCategories"] 	 = isset($lists["NGOCategories"]) 	  ? $lists["NGOCategories"] : null;
			$params["localBusinessCategories"] = isset($lists["localBusinessCategories"]) ? $lists["localBusinessCategories"] : null;
			$params["controller"] = Organization::CONTROLLER;
			$connectType = "members";

		} else if ($type == Project::COLLECTION){
			$element = Project::getById($id);
			$params["eventTypes"] = $list["eventTypes"];
			$params["listTypes"] = @$lists["organisationTypes"];
			$connectType = "contributors";
			$params["controller"] = Project::CONTROLLER;
		} else if ($type == Event::COLLECTION){
			$element = Event::getById($id);
			$params["eventTypes"] = $list["eventTypes"];
			$connectType = "attendees";
			$params["controller"] = Event::CONTROLLER;
			$invitedNumber=0;
			$attendeeNumber=0;
			if(@$element["links"][$connectType]){
				foreach ($element["links"][$connectType] as $uid => $e) {
					if(@$e["invitorId"]){
		  				if(@Yii::app()->session["userId"] && $uid==Yii::app()->session["userId"])
		  					$params["invitedMe"]=array("invitorId"=>$e["invitorId"],"invitorName"=>$e["invitorName"]);
		  				$invitedNumber++;
			  		} else
	  					$attendeeNumber++;

				}
			}
		} else if ($type == Person::COLLECTION){
			$element = Person::getById($id);
			$params["controller"] = Person::CONTROLLER;
		}

		if(isset($element["links"][$connectType])){
			$countStrongLinks=count($element["links"][$connectType]);
			$nbMembers=0;
			foreach ($element["links"][$connectType] as $key => $aMember) {
				if($nbMembers < 11){
					if($aMember["type"]==Organization::COLLECTION){
						$newOrga = Organization::getSimpleOrganizationById($key);
						if(!empty($newOrga)){
							if ($aMember["type"] == Organization::COLLECTION && @$aMember["isAdmin"]){
								$newOrga["isAdmin"]=true;  				
							}
							$newOrga["type"]=Organization::COLLECTION;
							//array_push($contextMap["organizations"], $newOrga);
							array_push($members, $newOrga);
						}
					} else if($aMember["type"]==Person::COLLECTION){
						if(!@$aMember["invitorId"]){
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
								//array_push($contextMap["people"], $newCitoyen);
								array_push($members, $newCitoyen);
							}
						}
					}
					$nbMembers++;
				} else {
					break;
				}
			}
		}
		$params["tags"] = Tags::getActiveTags();
		$params["element"] = $element;
		$params["members"] = $members;
		$params["type"] = $type;
		if($type==Event::COLLECTION){
			$params["countStrongLinks"]= @$attendeeNumber;
			$params["countLowLinks"] = @$invitedNumber;
		}
		else{
			$params["countStrongLinks"]= @$countStrongLinks;
			$params["countLowLinks"] = count(@$element["links"]["followers"]);
		}
		$params["countries"] = OpenData::getCountriesList();
		$page = "detail";
		if(Yii::app()->request->isAjaxRequest)
          echo $controller->renderPartial($page,$params,true);
        else 
			$controller->render( $page , $params );
    }
}
