<?php

class DeleteAction extends CAction
{
	/**
	 * Delete an entry from the organization table using the id
	 */
    public function run() {
    	//TODO SBAR : refactor : not use
		$result = array("result"=>false, "msg"=>Yii::t("common", "Uncorrect request"));
		if(Yii::app()->session["userId"])
		{
			$account = Organization::getById($_POST["id"]);
			if( $account && Yii::app()->session["userEmail"] == $account['ph:owner']) {
				PHDB::remove( Organization::COLLECTION,array("_id"=>new MongoId($_POST["id"])));
				//temporary for dev
				//TODO : Remove the association from all Ci accounts
				PHDB::update( Person::COLLECTION,array( "_id" => new MongoId(Yii::app()->session["userId"]) ) , array('$pull' => array("associations"=>new MongoId( $_POST["id"]))));

				$result = array("result"=>true,"msg"=>Yii::t("common", "Data saved"));
			}
		}
		Rest::json($result);
    }

}