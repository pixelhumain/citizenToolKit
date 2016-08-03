<?php

class DetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id) { 
    	$controller=$this->getController();
		$element = $type::getById($id);
		$members=array();
		$list = Lists::get(array("eventTypes"));
		if($type == Organization::CONTROLLER){
			$params["listTypes"] = isset($lists["organisationTypes"]) ? $lists["organisationTypes"] : null;
			$params["eventTypes"] = $list["eventTypes"];
			$params["public"] 			 = isset($lists["public"]) 			  ? $lists["public"] : null;
			$params["typeIntervention"]  = isset($lists["typeIntervention"])  ? $lists["typeIntervention"] : null;
			$params["NGOCategories"] 	 = isset($lists["NGOCategories"]) 	  ? $lists["NGOCategories"] : null;
			$params["localBusinessCategories"] = isset($lists["localBusinessCategories"]) ? $lists["localBusinessCategories"] : null;
			$params["type"] = Organization::COLLECTION;
			$params["controller"] = $type;
			$connectType = "members";

		} else if ($type == Project::COLLECTION){
			$params["eventTypes"] = $listEvent["eventTypes"];
			$params["organizationTypes"] = $lists["organisationTypes"];
			$connectType = "contributors";
		} else if ($type == Event::COLLECTION){
			$params["eventTypes"] = $list["eventTypes"];
			$connectType = "attendees";
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
					$nbMembers++;
				} else {
					break;
				}
			}
		}
		$params["tags"] = Tags::getActiveTags();
		$params["element"] = $element;
		$params["members"] = $members;
		$params["countStrongLinks"]= @$countStrongLinks;
		$params["countLowLinks"] = count(@$element["links"]["followers"]);
		$params["countries"] = OpenData::getCountriesList();
		$page = "detail";
		if(Yii::app()->request->isAjaxRequest)
          echo $controller->renderPartial($page,$params,true);
        else 
			    $controller->render( $page , $params );
    }
}
