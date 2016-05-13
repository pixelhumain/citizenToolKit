<?php

class Survey
{
	const TYPE_SURVEY = 'survey';
	const TYPE_ENTRY  = 'entry';
  	const STATUS_CLEARED 	= "cleared";
  	const STATUS_REFUSED 	= "refused";

	const COLLECTION = "surveys";
	const PARENT_COLLECTION = "actionRooms";
	const CONTROLLER = "survey";
	
	public static function getById($id) {
		$survey = PHDB::findOneById( self::COLLECTION ,$id );
		return $survey;
	}

    public static function moderateEntry($params) {
     	$res = array( "result" => false );
     	//check if user is set as admin
     	if( isset( Yii::app()->session["userId"] ))
     	{ 
     		if(self::isModerator(Yii::app()->session["userId"],$params["app"]))
     		{
		     	$survey = PHDB::findOne( PHType::TYPE_SURVEYS, array("_id"=>new MongoId($params["survey"])) );
		     	if( isset($survey["applications"][$params["app"]]["cleared"] ))
		     	{
		     		if($params["action"]){
		     			PHDB::update( PHType::TYPE_SURVEYS, 
		     									array("_id"=>new MongoId($params["survey"])),
		     									array('$unset' => array('applications.'.$params["app"].'.cleared' => true))
		     								);
		     			$res["msg"] = "EntryCleared";
		     			$res["result"] = true;
		     		} else {
		     			PHDB::update(  PHType::TYPE_SURVEYS, 
		     								    array("_id"=>new MongoId($params["survey"])),
		     									array('$set' => array('applications.'.$params["app"].'.cleared' => "refused"))
		     								);
		     			$res["msg"] = "EntryRefused";
		     		}
		     	} else 
		     		$res["msg"] = "Nothing to clear on this entry";
		     	

		     	$res["survey"] = PHDB::findOne( PHType::TYPE_SURVEYS, array("_id"=>new MongoId($params["survey"])) );
		     } else 
		     	$res["msg"] = "mustBeModerator";
	     } else 
	     	$res["msg"] = "mustBeLoggued";
	     
     	return $res;
     }
     public static function isModerator($userId,$app) {
     	$app = PHDB::findOne(PHType::TYPE_APPLICATIONS, array("key"=> $app ) );
     	$res = false;
     	if( isset($app["moderator"] ))
    		$res = ( isset( $userId ) && in_array(Yii::app()->session["userId"], $app["moderator"]) ) ? true : false;
    	return $res;
     }

     public static function deleteEntry($params){
     	$res = array( "result" => false );
     	if( isset( Yii::app()->session["userId"] ))
     	{ 
     		if( $survey = PHDB::findOne( PHType::TYPE_SURVEYS, array("_id"=>new MongoId($params["survey"])) ) ) 
     		{
	     		if(Person::isAppAdmin( Yii::app()->session["userId"] , $params["app"] ))
	     		{
			     	
	     			//first remove all children 
			     	$count = PHDB::count( PHType::TYPE_SURVEYS , array("survey" => $params["survey"]) );
			     	if( $count > 0){
				     	PHDB::remove( PHType::TYPE_SURVEYS, array("survey"=>$params["survey"]));
				     	$res["msg2"] = "Deleted ".$count." children entries" ;
					}

			     	//then remove the parent survey
	     			PHDB::remove( PHType::TYPE_SURVEYS,array("_id"=>new MongoId($params["survey"])));
	     			$res["msg"] = "Deleted";
	     			$res["result"] = true;

			     } else 
			     	$res["msg"] = "restrictedAccess";
		     } else
		     	$res["msg"] = "SurveydoesntExist";
	     } else 
	     	$res["msg"] = "mustBeLoggued";
		return $res;
     }

     public static function closeEntry($params){
     	$res = array( "result" => false );
     	if( isset( Yii::app()->session["userId"] ))
     	{ 
     		if( $survey = PHDB::findOne( Survey::COLLECTION, array("_id"=>new MongoId($params["id"])) ) ) 
     		{
	     		if( Yii::app()->session["userEmail"] == $survey["email"] ) //&& isset($survey["organizerId"]) && Yii::app()->session["userId"] == $survey["organizerId"] )
	     		{
			     	//then remove the parent survey
	     			PHDB::update( Survey::COLLECTION,
	     							array("_id" => $survey["_id"]), 
                          			array('$set' => array("dateEnd"=> time() )));
	     			$res["result"] = true;
			     } else 
			     	$res["msg"] = "restrictedAccess";
		     } else
		     	$res["msg"] = "SurveydoesntExist";
	     } else 
	     	$res["msg"] = "mustBeLoggued";
		return $res;
     }
}
?>