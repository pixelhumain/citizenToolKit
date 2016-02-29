<?php 
class Preference {
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
			
		$setType = $param["type"]; 
		$setValue = $param["value"];
		
		if($setValue == "hide"){
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
		}
		$res = array("result" => true, "msg" => Yii::t("common","Confidentiality param well updated"));
		return $res;

		
	}
}
