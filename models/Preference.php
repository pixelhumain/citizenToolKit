<?php 
class Preference {
	public static function updatePreferences($id, $type, $update=null) {
		PHDB::update($type, array("_id" => new MongoId($id)), 
			                          array('$unset' => array("preferences.seeExplanations" => "")
			                          ));
		$res = array("result" => true, "msg" => Yii::t("common","Your request is well updated"));
		return $res;
	}
}
