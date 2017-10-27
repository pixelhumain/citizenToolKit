<?php 

class RejectProposeInteropAction extends CAction
{
	function run() {

		$res = PHDB::findOneById('proposeOpenDataSource', $_GET["idpropose"]);

		Mail::rejectProposedInterop($res['url'], $res['userID'], Yii::app()->session["userId"], $res["description"]);

		PHDB::update('proposeOpenDataSource', array("_id" => new MongoId($_GET['idpropose'])) , 
            array('$set' => array("status" => "rejected")));

		Rest::json(array("result"=>true, "La proposition à bien été refusé"));
	}
}