<?php
class SaveAction extends CAction {
	

	public function run() {
		assert('!empty($_POST["type"]); //The type is mandatory');
	    assert('!empty($_POST["parentId"]); //The id is mandatory');
	    assert('!empty($_POST["parentType"]); //The action type is mandatory');
		if (! Person::logguedAndValid()) {
			echo json_encode(array('result'=>false,'error'=>Yii::t("common","Please Log in order to save a search and be alert !")));
			return;
		}
		if (Authorisation::canParticipate(Yii::app()->session["userId"], $_POST["parentType"], $_POST["parentId"])){
			$res = Bookmark::save($_POST);
			//echo json_encode(array('result'=>true, "msg" => Yii::t("document","Bookmark has been succesfully added")));
		} else 
		    $res =	array('result'=>false, "msg" => Yii::t("document","You are not allowed to save a bookmark in this context !"), "id" => $_POST["parentId"]);
		return Rest::json($res);
	}
}