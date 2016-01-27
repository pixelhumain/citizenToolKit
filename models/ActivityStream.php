<?php

/*
Activity Streams are made to keep track of activity inside any environment 

ACtivity stream sample
{
    "_id" : ObjectId("54b2a914f6b95c500b005f1f"),
    "type" : "esoModified",
    "groupId" : "126",
    "perimeterId" : "54895c3cf6b95c6c17003cd7",
    "verb" : "update",
    "date" : "2015-01-11 17:47:16",
    "timestamp" : 1420994836,
    "actor" : {
        "objectType" : "persons",
        "id" : "520931e2f6b95c5cd3003d6c"
    },
    "object" : {
        "objectType" : "eso",
        "displayName" : "Création d'un rapport SIGE automatisé"
    },
    "target" : {
        "objectType" : "perimeter",
        "id" : "54895c3cf6b95c6c17003cd7"
    },
    "notify" : {
        "objectType" : "persons",
        "id" : [ 
            "548ec7bbf6b95c8c23004b44"
        ],
        "displayName" : "Project Modified",
        "icon" : "fa-file",
        "url" : "javascript:editProject( projects[ \"548eb4c4f6b95c8823004296\" ] );"
    }
}

 */
class ActivityStream {

	const COLLECTION = "activityStream";
	/**
   *
   * @return [json Map] list
   */
	public static function addEntry($param)
	{
		if($param["type"]==self::COLLECTION){
			$news=$param;
		    PHDB::insert(News::COLLECTION, $news);
		}
	    $param["timestamp"] = new MongoDate(time());
	    PHDB::insert(self::COLLECTION, $param);
	}
	public static function getWhere($params) {
	  	 return PHDB::find( self::COLLECTION,$params,array("created"),null);
	}
	public static function getNotifications($param,$sort=array("timestamp"=>-1))
	{
	    return PHDB::findAndSort(self::COLLECTION, $param,$sort);
	}
	public static function getActivtyForObjectId($param,$sort=array("timestamp"=>-1))
	{
	    return PHDB::findAndSort(self::COLLECTION, $param,$sort,5);
	}
	
	
	public static function removeNotifications($id)
	{
	    $notif = PHDB::findOne(self::COLLECTION, array("_id"=> new MongoId($id) ) );
	    $res = array( "result"=>false,"msg"=>"Something went wrong : Activty Stream Not Found","id"=>$id );
	    if( isset($notif) && isset( $notif["notify"] ) && isset( $notif["notify"]["id"]) )
	    {
		    if( count($notif["notify"]["id"]) > 1 )
		    	//remove userid from array
		    	unset($notif["notify"]);
		    else
		    	unset($notif["notify"]);
			try{
			    unset($notif["_id"]);
			    PHDB::update( self::COLLECTION,
			                  array("_id"  => new MongoId($id) ), 
			                  array('$unset' => array("notify"=>true) ) );

			    $res = array( "result"=>true,"msg"=>"Removed succesfully" );
		    }
		    catch (Exception $e) {  
		          $res = array( "result"=>false,"msg"=>"Something went wrong :".$e->getMessage() );
		    } 
		}

		return Rest::json($res);
	}

	public static function removeNotificationsByUser()
	{
		try{
		    
		    $userNotifcations = PHDB::find( self::COLLECTION,array("notify.id"  => Yii::app()->session["userId"] ));
		    
		    foreach ($userNotifcations as $key => $value) 
		    {
		    	if(count($value["notify"]["id"]) == 1 )
		    		PHDB::update( self::COLLECTION,
				                  array("_id"  => $value["_id"] ), 
				                  array('$unset' => array("notify"=>true) ) );
		    	else
		    		PHDB::update( self::COLLECTION,
			                  	  array("_id"  => $value["_id"] ), 
			                  	  array('$pull' => array( "notify.id" => Yii::app()->session["userId"] )));
		    	
		    }
		    /*PHDB::updateWithOptions( self::COLLECTION,
					    			array("notify.id"  => Yii::app()->session["userId"] ),
					    			array('$pull' => array( "notify.id" => Yii::app()->session["userId"] )),
					    			array("multi"=>1));*/
			
		    $res = array( "result"=>true,"msg"=>"Removed succesfully" );
	    }
	    catch (Exception $e) {  
	        $res = array( "result"=>false,"msg"=>"Something went wrong :".$e->getMessage() );
	    } 
	

		return $res;
	}

	public static function addNotification($params)
	{
	    $notify =  array(
	        "objectType" => "persons",
	        "id" => $params["persons"],
	        "displayName" => $params["label"],
	        "icon" => $params["icon"],
	        "url" => $params["url"]
	    );
	    return $notify;
	}
	public static function removeObject($objectId,$type,$targetId=null,$targetType=null,$verb=null){
		$where=array("object.id"=>$objectId,"object.objectType"=> $type);
		if(@$targetId && $targetId!=null){
			$where["target.id"]=$targetId;
		}
		if(@$targetType && $targetType!=null){
			$where["target.objectType"]=$targetType;
		}
		if(@$verb && $verb != null){
			$where["verb"]=$verb;
		}
		$res = PHDB::remove(self::COLLECTION, $where);
		return $res;
	}
	public static function buildEntry($params)
    {
        $action = array(
            "type" => $params["type"],
            "verb" => $params["verb"],
            "author" => Yii::app()->session["userId"],
            "date" => new MongoDate(time()),
            "created" => new MongoDate(time())
        );

        if( isset( $params["object"] )){
            $action["object"] = array( 
                "objectType" => $params["object"]['type'],
                "id" => $params["object"]['id']
            );
        }

        if( isset( $params["target"] )){
            $action["target"] = array( 
                "objectType" => $params["target"]['type'],
                "id" => $params["target"]['id']
            );
        }

        if( isset( $params["ip"] ))
        	$action["author"]["ip"] = $params["ip"];
        	
		if($params["type"]==ActivityStream::COLLECTION){
			$action["scope"]["type"]="public";
	        if( isset( $params["cities"] ))
	        	$action["scope"]["cities"][$params["cities"]] = $params["cities"];
			if( isset( $params["geo"] ))
	        	$action["scope"]["geo"] = $params["geo"];
		}
        if( isset( $params["label"] ))
        	$action["object"]["displayName"] = $params["label"];
		if (isset ($params["tags"]))
			$action["tags"] = $params["tags"];
      return $action;
    }

}