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
		if($setValue=="true"){
			$setValue =true;
		}
		else if($setValue=="false"){
			$setValue=false;
		}

		$preferences["privateFields"] = $privateFields;
		$preferences["publicFields"] = $publicFields;

		if($setType == "isOpenData"){
			$preferences["isOpenData"] = $setValue;
		}else{
			$preferences["isOpenData"] = $context["preferences"]["isOpenData"];
		}
		if($setType == "isOpenEdition"){
			$preferences["isOpenEdition"] = $setValue;
		}else{
			if($type != Person::COLLECTION)
				$preferences["isOpenEdition"] = (empty($context["preferences"]["isOpenEdition"])?false:$context["preferences"]["isOpenEdition"]);
		}

		if(self::isOpenData($preferences))
			Badge::addAndUpdateBadges("opendata", $id, $type);
		else
			Badge::delete("opendata", $id, $type);
		
		/*PHDB::update($type, array("_id" => new MongoId($id)), 
		    array('$set' => array("preferences.privateFields" => $privateFields, "preferences.publicFields" => $publicFields)));*/		
		PHDB::update($type, array("_id" => new MongoId($id)), 
		    array('$set' => array("preferences" => $preferences)));
		
		$res = array("result" => true, "msg" => Yii::t("common","Confidentiality param well updated"));
		return $res;
	}


	public static function isOpenData($preferences) {
		$isOpenData = false ;
		if(!empty($preferences["isOpenData"]))
			$isOpenData = true ;
		return $isOpenData;
	}

	public static function isOpenEdition($preferences) {
		$isOpenData = false ;
		if(@$preferences["isOpenEdition"])
			$isOpenData = true ;
		return $isOpenData;
	}


	
	
}
