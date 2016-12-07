<?php 
class Preference {
	public static function getPreferencesByTypeId($id, $type){

		if($type == City::COLLECTION){
			$city = City::getByUnikey($id);
			$id = $city["_id"];
		}

		$entity = PHDB::findOneById( $type ,$id, array("preferences" => 1));
		$preferences = (empty($entity["preferences"])?array():$entity["preferences"]);
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
			$preferences["isOpenData"] = ((empty($context["preferences"]["isOpenData"]))?false:true);
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

		ActivityStream::saveActivityHistory(ActStr::VERB_UPDATE, $id, $type, $setType, self::valueActivityStream($setValue));
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

	public static function valueActivityStream($setValue) {
		$value = "";
		if($setValue == true)
			$value = Yii::t("common","True");
		else if($setValue == false)
			$value = Yii::t("common","False");
		else 
			$value = $setValue;

		return $value;
	}

	/*public static function getStatusPreference($preferences, $namePref) {
		$result = "mask";
		if(@$preferences["publicFields"] && in_array($namePref, $preferences["publicFields"]))
			$result = "public";
		else if(@$preferences["privateFields"] &&  in_array($namePref, $preferences["privateFields"]))
			$result = "private";
		return $result;
	}*/


	public static function showPreference($element, $elementType, $namePref, $userId) {
		//$status = self::getStatusPreference($element["preferences"], $namePref);
		$result = false;
		
		$eltId = ((!empty($element["_id"]))?(string)$element["_id"]: (string)$element["id"]);
		
		if(empty($element["preferences"])){
			$element["preferences"] = self::getPreferencesByTypeId($eltId, $elementType) ;
		}
		//mask
		if($elementType==Person::COLLECTION && $eltId == $userId){
			$result = true;
		}//public
		else if($result == false && self::isPublic($element, $namePref)){
			$result = true;
		}//private
		else if( $result == false && 
				@$element["preferences"]["privateFields"] && 
				in_array($namePref, $element["preferences"]["privateFields"]) && 
				( $eltId == $userId || Link::isLinked($eltId, $elementType, $userId, @$element["links"]) ) )
			$result = true;
		return $result;
	}


	public static function isPublic($element, $namePref) {
		$result = false;
		if(@$element["preferences"]["publicFields"] && in_array($namePref, $element["preferences"]["publicFields"]))
			$result = true;
		return $result;
	}


	public static function initPreferences($type) {
		$preferences = array();
		$preferences["isOpenData"] = true;
		if($type == Person::COLLECTION){
			$preferences["publicFields"] = array("locality", "directory");
			$preferences["privateFields"] = array("birthDate", "email", "phone");
		}else{
			$preferences["isOpenEdition"] = true;
		}
		return $preferences;
	}
	
}
