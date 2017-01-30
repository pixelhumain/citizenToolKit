<?php

class UpdateAction extends CAction
{
	/**
	* Update an information field for an project
	*/
    public function run() {
		assert('!empty($_POST["projectId"]) != ""; //The project Id is mandatory');
		if ( ! Person::logguedAndValid() ) {
			return Rest::json(array("result"=>false, "msg"=>"You are not loggued with a valid user !"));
		}

		$projectId = $_POST["projectId"];
		unset($_POST["projectId"]);
		$res = array("result"=>false, "msg"=>Yii::t("common", "Something went wrong!"));
		try {
			$project = Project::getAndCheckProject($_POST, Yii::app()->session["userId"],true);
			$res = Project::update($projectId, $project, Yii::app()->session["userId"]);
		} catch (CTKException $e) {
			$res = array("result"=>false, "msg"=>$e->getMessage());
		}

		Rest::json($res);
    }
}