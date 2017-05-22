<?php 
//Class to manage Actions in the DDA
//TODO : rename tasks ?
class Actions {

	const COLLECTION        = "actions";

    const TYPE_ACTIONS 		= "actions"; //things to do 
	const TYPE_ACTION 		= "action"; //things to do
	const ACTIONS_PARENT	= "rooms";

	//ACTION STATES
	const ACTION_TODO = "todo";
	const ACTION_INPROGRESS = "inprogress";
	const ACTION_LATE = "late";	
	const ACTION_CLOSED = "closed";	

	public static function getById($id) {
	  	return PHDB::findOne( self::COLLECTION ,array("_id"=>new MongoId($id)));
	}

    public static function canAdministrate($userId, $id) {
        $actionRoom = self::getById($id);

        $parentId = @$actionRoom["parentId"];
        $parentType = @$actionRoom["parentType"];

        $isAdmin = false;
        if( ( $parentType == Organization::COLLECTION && Authorisation::isOrganizationAdmin ( $userId , $parentId ) )
            || ( $parentType == Project::COLLECTION && Authorisation::isProjectAdmin( $userId , $parentId ) )
            || ( $parentType == Event::COLLECTION && Authorisation::isEventAdmin( $userId , $parentId ) ) )
            $isAdmin = true;
        return $isAdmin;
    }

    public static function closeAction($params){
     	$res = array( "result" => false );
     	if( isset( Yii::app()->session["userId"] ))
     	{ 
     		if( $action = PHDB::findOne( self::COLLECTION, array("_id"=>new MongoId($params["id"])) ) ) 
     		{
	     		if( Yii::app()->session["userEmail"] == $action["email"] ) 
	     		{
			     	//then remove the parent survey
			     	$status = ( @$action["status"] == self::ACTION_CLOSED) ? self::ACTION_INPROGRESS : self::ACTION_CLOSED; 
	     			PHDB::update( self::COLLECTION,
	     							array("_id" => $action["_id"]), 
                          			array('$set' => array("status"=> $status )));
                    Action::updateParent($_POST['id'], self::COLLECTION);
	     			$res["result"] = true;
			     } else 
			     	$res["msg"] = "restrictedAccess";
		     } else
		     	$res["msg"] = "SurveydoesntExist";
	     } else 
	     	$res["msg"] = "mustBeLoggued";
		return $res;
    }

     /**
        * must be part of the organisation or project to take action 
        * on city actions anyone can participate
        * @return [json Map] list
        */
    public static function assignMe($params) {
     	$res = array( "result" => false );
     	if( isset( Yii::app()->session["userId"] ))
     	{ 
     		if( $action = PHDB::findOne( self::COLLECTION, array("_id"=>new MongoId($params["id"])) ) ) 
     		{
	     		if( Authorisation::canParticipate(Yii::app()->session["userId"], $action["parentType"], $action["parentId"]) ) 
	     		{
			     	$res = Link::connect($params["id"], self::COLLECTION,Yii::app()->session["userId"], Person::COLLECTION, Yii::app()->session["userId"], "contributors", true );
                    Action::updateParent($_POST['id'], self::COLLECTION);
			     } else 
			     	$res["msg"] = "restrictedAccess";
		     } else
		     	$res["msg"] = "SurveydoesntExist";
	     } else 
	     	$res["msg"] = "mustBeLoggued";
		return $res;
    }

    /**
     * Delete an action and its children (comments...)
     * @param String $id id of the action to delete
     * @param String $userId userId making the delete
     * @return array result => boolean, msg => String
     */
    public static function deleteAction($id, $userId){
     	$res = array( "result" => false, "msg" => "Something went wrong : contact your admin !");;
     	
        $action = self::getById($id);
        if (empty($action)) return array("result" => false, "msg" => "The action does not exist");
        
        if (! self::canAdministrate($userId, $id)) return array("result" => false, "msg" => "You must be admin of the parent of this action if you want delete it");

        //Remove all comments linked
        if (isset($action["comment"])) {
            $resComment = Comment::deleteAllContextComments($id, self::COLLECTION, $userId);
        } 

        if (isset($resComment["result"]) && ! @$resComment["result"]) return $resComment;

        //Remove the entry (survey)
        if (PHDB::remove(self::COLLECTION,array("_id"=>new MongoId($id)))) {
            $res = array( "result" => true, "msg" => "The action has been deleted with success");
        } 

        return $res;
    }

    /**
     * Delete all the actions of the action room
     * @param String $actionRoomId 
     * @param String $userId 
     * @return array result => boolean, msg => String
     */
    public static function deleteAllActionsOfTheRoom($actionRoomId, $userId) {
    	$canDelete = ActionRoom::canAdministrate($userId, $actionRoomId);
		if ($canDelete) {
			$where = array("room" => $actionRoomId);
			$actions = PHDB::find(self::COLLECTION, $where);
			foreach ($actions as $id => $action) {
				$res = self::deleteAction($id, $userId);
			}
		} else {
			return array("result"=>false, "msg"=>Yii::t("common","You are not allowed to delete this action room"));
		}
		
		if ($res["result"]) {
			$res = array("result"=>true, "msg"=>Yii::t("common","The actions of this action room have been deleted with success"));
		} 
		
		return $res;
    }


}
