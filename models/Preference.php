<?php 
class Preference {
	public static function getPreferencesByTypeId($id, $type){
		$preferences = PHDB::findOneById( $type ,$id, array("preferences" => 1));
		return $preferences;
	}
	
	public static function updatePreferences($id, $type, $update=null) {
		PHDB::update($type, array("_id" => new MongoId($id)), 
			                          array('$unset' => array("preferences.seeExplanations" => "")
			                          ));
		$res = array("result" => true, "msg" => Yii::t("common","Your request is well updated"));
		return $res;
	}
	
	public static function updateConfidentiality($id, $type, $param){
		if ($type == Person::COLLECTION)
			$context = Person::getById($id);
		if($type == Organization::COLLECTION){
			$id = $param["idEntity"];
			$context = Organization::getById($id);
		}
		if($type == Event::COLLECTION){
			$id = $param["idEntity"];
			$context = Event::getById($id);
		}
		if($type == Project::COLLECTION){
			$id = $param["idEntity"];
			$context = Project::getById($id);
		}
			
			
		$setType = $param["type"]; 
		$setValue = $param["value"];
		$res = array();
		/*if($setType == "isOpenData"){
			$setValue = (($setValue == "public")? true:false);
			if ($type == Person::COLLECTION)
				Person::updatePersonField($id, $setType, $setValue, Yii::app()->session["userId"]);
			if($type == Organization::COLLECTION)
				Organization::updateOrganizationField($id, $setType, $setValue, Yii::app()->session["userId"]);
		}else{*/
			$publicFields = array();
			$privateFields = array();
			
		    if(@$context["preferences"]["publicFields"] && !empty($context["preferences"]["publicFields"])){
				$publicFields=$context["preferences"]["publicFields"];
				//if(in_array($setType, $publicFields)) {
				foreach ($publicFields as $key => $value) {
				    if ($setType === $value) {
				    	unset($publicFields[$key]);
				    }
				}	
			}
			if(@$context["preferences"]["privateFields"] && !empty($context["preferences"]["privateFields"]))			{
				$privateFields=$context["preferences"]["privateFields"];
				foreach ($privateFields as $key => $value) {
				    if ($setType === $value) {
				    	unset($privateFields[$key]);
				    }
				}		
			} 
			if($setValue=="public"){
				$publicFields[]=$setType;
			}
			if($setValue=="private"){
				$privateFields[]=$setType;
			}
			
			PHDB::update($type, array("_id" => new MongoId($id)), 
			    array('$set' => array("preferences.privateFields" => $privateFields, "preferences.publicFields" => $publicFields)));		
			
			/*if($setValue == "hide"){
				PHDB::update($type, array("_id" => new MongoId($id)), 
				    array('$unset' => array("preferences.publicFields.".$setType => "","preferences.privateFields.".$setType => "")
				));

			} else if($setValue == "private"){
				PHDB::update($type, array("_id" => new MongoId($id)), 
				     array('$set'=> array("preferences.privateFields.".$setType => true), '$unset' => array("preferences.publicFields.".$setType => ""))
				);
				
			} else if($setValue == "public"){
				PHDB::update($type, array("_id" => new MongoId($id)), 
				    array('$unset' => array("preferences.privateFields.".$setType => ""),'$set'=> array("preferences.publicFields.".$setType => true))
				);
			}*/
		//}
		
		$res = array("result" => true, "msg" => Yii::t("common","Confidentiality param well updated"));
		return $res;
	}


	
	
}
