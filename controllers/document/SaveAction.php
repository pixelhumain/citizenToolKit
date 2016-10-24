<?php
class SaveAction extends CAction {

	public function run() {
		$res = array();

		if (Person::logguedAndValid()) {
			$params = array(
				"id" => $_POST['id'],
		  		"type" => $_POST['type'],
		  		"folder" => $_POST['folder'],
		  		"moduleId" => $_POST['moduleId'],
		  		"name" => $_POST['name'],
		  		"size" => (int) $_POST['size'],
		  		"contentKey" => $_POST["contentKey"],
		  		"author" => Yii::app()->session["userId"]
		    );
		    if(@$_POST["parentType"])
			    $params["parentType"] = $_POST["parentType"];
		    if(@$_POST["parentId"])
			    $params["parentId"] = $_POST["parentId"];			
			if(@$_POST["formOrigin"])
			    $params["formOrigin"] = $_POST["formOrigin"];
		    $res = Document::save($params);
		} else {
			$res = array("result" => false, "msg" => Yii::t("common","Please Log in order to update document !"));
		}

		return Rest::json($res);
	}

}