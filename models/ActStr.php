<?php
class ActStr {

	//All events taht can be loggued into the activity stream
	const TEST = "test"; 
	const ICON_QUESTION = "fa-question";
    const ICON_SHARE = "fa-share-alt";
    const ICON_COMMENT = "fa-comment";
    const ICON_CLOSE = "fa-times";

    const VIEW_PAGE = "viewPage";

    const VERB_VIEW = "view";
    const VERB_ADD = "add";
    const VERB_UPDATE = "update";
    const VERB_CREATE = "create";
    const VERB_DELETE = "delete";
      
    const VERB_JOIN = "join";
    const VERB_WAIT = "wait";
    const VERB_LEAVE = "leave";
    const VERB_INVITE = "invite";
    const VERB_ACCEPT = "accept";
    const VERB_CLOSE = "close";
    const VERB_SIGNIN = "signin";
      
    const VERB_HOST = "host";
    const VERB_FOLLOW = "follow";
    const VERB_CONFIRM = "confirm";
    const VERB_AUTHORIZE = "authorize";
    const VERB_ATTEND = "attend";
    const VERB_COMMENT = "comment";
    
    const VERB_POST = "post";

    const TYPE_URL = "url";
	
	public static function buildEntry($params)
    {
        $action = array(
            "type" => $params["type"],
            "verb" => $params["verb"],
            "author" => Yii::app()->session["userId"],
            "date" => new MongoDate(time()),
            "created" => new MongoDate(time())
        );

      /*  if( isset( $params["author"] )){
            $action["author"] = array( 
                "objectType" => $params["author"]['type'],
                "id" => $params["author"]['id']
            );
        }*/

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

        //if( isset( $action["author"] ) && isset( $params["ip"] ))
        //	$action["author"]["ip"] = $params["ip"];
        	
		if($params["type"]==ActivityStream::COLLECTION){
			$action["scope.type"]="public";
	        if( isset( $params["cities"] ))
	        	$action["scope"]["cities"] = $params["cities"];
			if( isset( $params["geo"] ))
	        	$action["scope"]["geo"] = $params["geo"];
		}
        if( isset( $params["label"] ))
        	$action["object"]["displayName"] = $params["label"];
		if (isset ($params["tags"]))
			$action["tags"] = $params["tags"];
      return $action;
    }

    public static function viewPage ($url)
    {
        $asParam = array(
            "type" => ActStr::VIEW_PAGE, 
            "verb" => ActStr::VERB_VIEW,
            "actorType" => Person::COLLECTION,
            "objectType" => ActStr::TYPE_URL,
            "id" => $url,
            "ip" => $_SERVER['REMOTE_ADDR']
        );
        $action = self::buildEntry($asParam);
        ActivityStream::addEntry($action);
    }


    public static function getParamsByVerb($verb,$ctrl,$target,$currentUser)
    {
        $res = false;
        $verbParams = array(
            ActStr::VERB_CLOSE => array("label" => $target["name"]." ".Yii::t("common","has been disabled by")." ".Yii::app()->session['user']['name'],
                                        "url"   => $ctrl.'/detail/id/'.$target["id"]), 
            ActStr::VERB_POST => array("label"  => $target["name"]." : ".Yii::t("common","new post by")." ".Yii::app()->session['user']['name'],
                                        "url"   => 'news/index/type/'.$target["type"].'/id/'.$target["id"].'?isSearchDesign=1'), 
            ActStr::VERB_FOLLOW => array("label" => Yii::app()->session['user']['name'],
                                        "url"   => Person::CONTROLLER.'/detail/id/'.Yii::app()->session['userId']), 
            ActStr::VERB_WAIT => array("label" => Yii::app()->session['user']['name']." ".Yii::t("common","wants to join")." ".$target["name"],
                                        "url"   => $ctrl.'/directory/id/'.$target["id"].'?tpl=directory2'), 
            ActStr::VERB_AUTHORIZE => array("label" => Yii::app()->session['user']['name']." ".Yii::t("common","wants to administrate")." ".$target["name"],
                                        "url"   => $ctrl.'/directory/id/'.$target["id"].'?tpl=directory2'), 
            ActStr::VERB_JOIN => array("label" => Yii::app()->session['user']['name']." ".Yii::t("common","participates to the event")." ".$target["name"],
                                        "url"   => 'news/detail/id/'.$target["id"]), 
            ActStr::VERB_COMMENT => array("label" => Yii::app()->session['user']['name']." ".Yii::t("common","has commented your post"),
                                        "url"   => $ctrl.'/detail/id/'.$target["id"])
        );
        
        if( isset( $verbParams[$verb]) ) {
            $res = $verbParams[$verb];
            if( $verb == ActStr::VERB_FOLLOW ){
                if($target["type"]==Person::COLLECTION)
                    $specificLab = Yii::t("common","is following you");
                else
                    $specificLab = Yii::t("common","is following")." ".$target["name"];
                $res["label"] .= " ".$specificLab;
            }
        }

        return $res;
    }
}