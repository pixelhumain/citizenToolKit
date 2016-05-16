<?php 
class ActionRoom {

	const COLLECTION 		= "actionRooms";
	const CONTROLLER 		= "rooms";
	
	const TYPE_SURVEY 		= "survey"; //sondage à la Google Forms
	const TYPE_DISCUSS 		= "discuss"; // systeme de discussioin voir avec dialoguea
	const TYPE_BRAINSTORM 	= "proposals"; //systeme de rpopositions pour prendre des décision
	const TYPE_VOTE 		= "vote"; //vote
	const TYPE_DISTRIBUTE	= "distribute"; //vote par distribution sur des proposition
	
	const TYPE_ACTIONS 		= "actions"; //things to do 
	const TYPE_ACTION 		= "action"; //things to do 
	const COLLECTION_ACTIONS= "actions";
	const ACTIONS_PARENT	= "rooms";
	/**
	 * get a action room By Id
	 * @param String $id : is the mongoId of the action room
	 * @return array Document of the action room
	 */
	public static function getById($id) {
	  	return PHDB::findOne( self::COLLECTION,array("_id"=>new MongoId($id)));
	}
	public static function getActionById($id) {
	  	return PHDB::findOne( self::COLLECTION_ACTIONS,array("_id"=>new MongoId($id)));
	}

	public static function getWhereSortLimit($params,$sort,$limit=1) {
	  	return PHDB::findAndSort( self::COLLECTION,$params,$sort,$limit);
	}

	public static function getSingleActionRoomByOrgaParent($idOrga){
		error_log("idOrga " . $idOrga);
		return PHDB::findOne( self::COLLECTION, 
										array("parentId"=> $idOrga, 
											  //"parentType" => "organizations",
											  //"type" => "vote"
											  ));
	}

	public static function canParticipate($userId,$id=null,$type=null) {
		$showAddBtn = false;
        if( ( $type == Organization::COLLECTION && Authorisation::isOrganizationMember( $userId , $id ) )
            || ( $type == Project::COLLECTION && Authorisation::isProjectMember( $userId , $id ) )
            || ( $type == Event::COLLECTION && Authorisation::isEventMember( $userId , $id ) ) )
            $showAddBtn = true;
	  	return $showAddBtn;
	}

	public static function isModerator($userId,$app) {
     	$app = PHDB::findOne(PHType::TYPE_APPLICATIONS, array("key"=> $app ) );
     	$res = false;
     	if( isset($app["moderator"] ))
    		$res = ( isset( $userId ) && in_array(Yii::app()->session["userId"], $app["moderator"]) ) ? true : false;
    	return $res;
     }
}
