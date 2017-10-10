<?php 

class ProposeOpenDataSourceAction extends CAction
{
	function run() {

		PHDB::insert( "proposeOpenDataSource", array(
			// "email" => "contact@communecter.org",
	        "url" => $_POST["url"],
	        "description" => $_POST["description"],
	        "status" => "proposed",
	        "userID" => Yii::app()->session["userId"],
	    	// "type" => ActionRoom::TYPE_VOTE ,
	   		"created" => new MongoDate(time()) ) 
		);

		Mail::proposeInteropSource($_POST['url'], null, Yii::app()->session["userId"], $_POST['description']);

		Rest::json(array("result"=>true, "Votre proposition à bien était pris en compte merci d'attendre la réponse d'unadministrateur de Communecter"));
	}
}