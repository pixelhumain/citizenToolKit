<?php 
class Preference {

	public static $listPref = array( "email", "locality", "phone", "directory", "birthDate" );

	public static function getPreferencesByTypeId($id, $type){

		if($type == City::COLLECTION){
			$city = City::getByUnikey($id);
			$id = $city["_id"];
		}

		$entity = PHDB::findOneById( $type ,$id, array("preferences" => 1));
		$preferences = (empty($entity["preferences"])?array():$entity["preferences"]);
		return $preferences;
	}
	
	public static function updatePreferences($id, $type,$preferenceName=null, $preferenceValue=null, $preferenceSubName=null) {
		$action='$set';
		$update=array();
		if(!@$preferenceName || empty($preferenceName)){
			$action='$unset';
			$update=array("preferences.seeExplanations"=>"");
		}else{
			if($preferenceValue==="true" || $preferenceValue=="default"){
				$action='$unset';
				$preferenceValue="";
			}
			$update=array("preferences.".$preferenceName=>$preferenceValue);
			if(!empty($preferenceSubName))
				$update=array("preferences.".$preferenceName.".".$preferenceSubName=>$preferenceValue);
		}

		PHDB::update($type, array("_id" => new MongoId($id)), array($action => $update)
		);
		$res = array("result" => true, "msg" => Yii::t("common","Your request is well updated"));
		return $res;
	}

	public static function updateSettings($userId, $params){
		$settings=array("name"=>$params["settings"],"value"=>$params["value"]);
		$parentId=$params["id"];
		$parentType=$params["type"];
		$childId=(@$params["childId"]) ? $params["childId"] : Yii::app()->session["userId"];
		$childType=(@$params["childType"]) ? $params["childType"] : Person::COLLECTION; 
		$parentConnectAs=Link::$linksTypes[$parentType][$childType];
		$childConnectAs=Link::$linksTypes[$childType][$parentType];
		if($settings["value"]=="default"){
			//Add notification - email label in parent link
			Link::disconnect($parentId, $parentType, $childId, $childType,Yii::app()->session["userId"], $parentConnectAs, null, $settings);
	 		//Add notification - email label in child link
	 		Link::disconnect($childId, $childType, $parentId, $parentType, Yii::app()->session["userId"], $childConnectAs, null, $settings);
		}else{
			//Add notification - email label in parent link
			Link::connect($parentId, $parentType, $childId, $childType,Yii::app()->session["userId"], $parentConnectAs, null, null, null,null, null, $settings);
	 		//Add notification - email label in child link
	 		Link::connect($childId, $childType, $parentId, $parentType, Yii::app()->session["userId"], $childConnectAs, null, null, null, null, null, $settings);
 		}
 		return array("result" => true, "msg" => Yii::t("common","Your request is well updated"));
	}

	public static function updateConfidentiality($id, $type, $param){
		//if ($type == Person::COLLECTION){
		$id = $param["idEntity"];
		$context = Element::getElementSimpleById($id, $type, null, array("preferences"));
		//}

		/*if($type == Organization::COLLECTION){
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
		}*/

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
			    	array_splice($publicFields, $key, 1);
			    	//unset($publicFields[$key]);
			    }
			}	
		}

		if(@$context["preferences"]["privateFields"] && !empty($context["preferences"]["privateFields"]))			{
			$privateFields=$context["preferences"]["privateFields"];
			foreach ($privateFields as $key => $value) {
			    if ($setType === $value) {
			    	array_splice($privateFields, $key, 1);
			    	//unset($privateFields[$key]);
			    }
			}		
		}
		
		if($setValue=="public"){
			array_push($publicFields,$setType);
		}
		if($setValue=="private"){
			array_push($privateFields,$setType);
			//$privateFields[]=$setType;
		}
		if($setValue=="true"){
			$setValue =true;
		}
		else if($setValue=="false"){
			$setValue=false;
		}
		if(!empty($privateFields))
			$preferences["privateFields"] = $privateFields;
		if(!empty($publicFields))
			$preferences["publicFields"] = $publicFields;
		
		if($setType == "isOpenData"){
			$preferences["isOpenData"] = $setValue;
		}else{
			$preferences["isOpenData"] = ((empty($context["preferences"]["isOpenData"]))?false:true);
		}
		if($setType == "private"){
			$preferences["private"] = $setValue;
		}else{
			if(!empty($context["preferences"]["private"]))
				$preferences["private"] = true;
		}
		if($setType == "isOpenEdition"){
			$preferences["isOpenEdition"] = $setValue;
			PHDB::update( $type,  array("_id" => new MongoId($id)), 
		 										array('$unset' => array("hasRC"=>"") ));
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
		$result = PHDB::update($type, array("_id" => new MongoId($id)), 
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
	public static function isPublicElement($preferences) {
		$isPublic = true ;
		if(@$preferences["private"])
			$isPublic = false ;
		return $isPublic;
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


	public static function clearByPreference($element, $elementType, $userId) {
		$eltId = ( (!empty($element["_id"])) ? (string)$element["_id"] : (string)$element["id"] );
		if(!empty($eltId)){
			if(empty($element["preferences"])){
				$element["preferences"] = self::getPreferencesByTypeId($eltId, $elementType) ;
			}

			foreach (self::$listPref as $key => $namePref) {
				if( !self::showPreference($element, $elementType, $namePref, $userId) ){
					if($namePref == "locality"){
						unset($element["address"]);
						unset($element["geo"]);
						unset($element["geoPosition"]);
					}else if($namePref == "phone"){
						unset($element["telephone"]);
					}else if($namePref == "directory"){
						unset($element["links"]);
					}else{
						unset($element[$namePref]);
					}
				}
			}
		}
		
		return $element;
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
