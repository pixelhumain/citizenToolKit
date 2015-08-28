<?php

class DisableAction extends CAction
{
	 /**
	 * Deletes an Organization
	 * Remove any links on any person linked to this mongoid 
	 * @param type $id : is the mongoId of the organisation to be deleted
	 */
    public function run($id) {
    	$result = array("result"=>false, "msg"=>Yii::t("common", "Incorrect request"));
		if(Yii::app()->session["userId"] || Yii::app()->session["userIsAdmin"] )
		{
			$organization = Organization::getById( $id );
			if( $organization && Yii::app()->session["userId"] == $organization['creator'] ) {
				
				PHDB::update( Organization::COLLECTION, array("_id"=>new MongoId($id)) , 
														array('$set' => array("disabled"=> true)));
				//add notification to all members 

				$result = array("result"=>true,"msg" => Yii::t("common", "Organization Disabled") );
			}
		}
		Rest::json($result);
    }

}