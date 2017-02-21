<?php
/*
- actions are saved on any needed element in any collection

 */
class Action
{
    const NODE_ACTIONS          = "actions";

    const ACTION_ROOMS          = "actionRooms";
    const ACTION_ROOMS_TYPE_SURVEY = "survey";

    const ACTION_MODERATE       = "moderate";
    const ACTION_VOTE_UP        = "voteUp";
    const ACTION_VOTE_ABSTAIN   = "voteAbstain";
    const ACTION_VOTE_UNCLEAR   = "voteUnclear";
    const ACTION_VOTE_MOREINFO  = "voteMoreInfo";
    const ACTION_VOTE_DOWN      = "voteDown";
   
    //const ACTION_VOTE_BLOCK   = "voteBlock";
    const ACTION_PURCHASE       = "purchase";
    /*const ACTION_INFORM       = "inform";
    const ACTION_ASK_EXPERTISE  = "expertiseRequest";*/
    const ACTION_COMMENT        = "comment";
    const ACTION_REPORT_ABUSE   = "reportAbuse";
    const ACTION_FOLLOW         = "follow";

    /**
     * - can only add an action once vote , purchase, .. 
     * - check user and element existance 
     * - QUESTION : should actions be application inside
     * @param String $userId : the id of the user doing the action
     * @param String $id : the id of the element it applied on
     * @param String $collection : Location of the element
     * @param String $action : Type of the action
     * @param String $reason : Detail or comment
     * @param boolean $unset : if the user already did the action, the action will be unset
     * @param boolean $multiple : true : the user can do multiple action, else can not.
     * @return array result (result, msg)
     */
        public static function addAction( $userId=null , $id=null, $collection=null, $action=null, $unset=false, $multiple=false, $details=null){
       
        $user = Person::getById($userId);
        $element = ($id) ? PHDB::findOne ($collection, array("_id" => new MongoId($id) )) : null;
        $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');

        if($user && $element){
            //check user hasn't allready done the action or if it's allowed
            if( $unset 
                || !isset( $element[ $action ] ) 
                || ( !$multiple && isset( $element[ $action ] ) && !in_array( (string)$user["_id"] , $element[ $action ] )
                || $multiple ) ){
                

                //Add or remove
                $dbMethod = '$set';
                if($unset){
                    $dbMethod = '$unset';
                    if(($action=="voteUp" || $action=="voteDown") && (!isset( $element[$action][$userId])))
    	                throw new CTKException("Well done ! Stop playing and join us to help the construction of this common!");
                }else{
	            	if(($action=="voteUp" || $action=="voteDown" || $action=="reportAbuse") && (isset($element[$action][$userId])))
    	                throw new CTKException("Well done ! Stop playing and join us to help the construction of this common!");
                }

                // Additional info
                // can contain a 
                // comment, date
                if (!empty($details) && is_array($details))
                    $details = array_merge($details, array('date' => new MongoDate(time()))) ; 
                else 
                    $details = array('date' => new MongoDate(time()));
                //$mapUser[ self::NODE_ACTIONS.".".$collection.".".$action.".".(string)$element["_id"] ] = $details ;
                $mapUser[self::NODE_ACTIONS.".".$collection.".".(string)$element["_id"].".".$action ] = $action ;
                //update the user table => adds or removes an action
                PHDB::update ( Person::COLLECTION , array( "_id" => $user["_id"]), 
                                                    array( $dbMethod => $mapUser));

                //Decrement when removing an action instance
                if($unset){
                    $dbMethod = '$unset';
                    $inc = -1;
                }//Push unique user Ids into action node list + increment
                elseif($multiple == true)
                {
                    $dbMethod = '$addToSet';
                    $inc = 1;
                }//Save unique user Id and details into action + increment
                else{
                    $dbMethod = '$set';
                    $inc = 1;
                }
                
                if($unset){
                    PHDB::update ( $collection, array( "_id" => new MongoId($element["_id"]) ), 
                                                array( $dbMethod => array(  $action.".".Yii::app()->session["userId"] => 1),
                                                       '$inc'=>array( $action."Count" => $inc),
                                                       '$set'=>array( "updated" => time(),
                                                                      "modified" => new MongoDate(time()))
                                                       ));
                    // DELETE IN NOTIFICATION REMOVE LIKE
                }
                else{
                    $mapObject[ $action.".".(string)$user["_id"] ] = $details ;
                    $params = array();

                    if( $dbMethod == '$set'){
                        $mapObject["updated"] = time();
                        $mapObject["modified"] = new MongoDate(time());
                    }
                    else
                        $params['$set'] = array( "updated" => time(), "modified" => new MongoDate(time()) );

                    $params[$dbMethod] = $mapObject;
                    $params['$inc'] = array( $action."Count" => $inc);

                    PHDB::update ($collection, 
                                    array("_id" => new MongoId($element["_id"])), 
                                    $params);
                    //NOTIFICATION LIKE AND DISLIKE
                    if($action == "voteUp") 
                       $verb = ActStr::VERB_LIKE;
                    else if($action == "voteDown")
                        $verb = ActStr::VERB_UNLIKE;
                    if(@$verb && $collection != Survey::COLLECTION){
                        $objectNotif=null;
                        if($collection==Comment::COLLECTION){
                            $target=array("type"=>$element["contextType"], "id"=>$element["contextId"]);
                            $objectNotif=array("type"=>$collection,"id"=>(string)$element["_id"]);
                        } else {
                            $target=array("type"=>$collection, "id"=>(string)$element["_id"]);
                            if(@$element["targetIsAuthor"] || @$target["object"])
                                $target["targetIsAuthor"]=true;
                        }
                        Notification::constructNotification($verb, array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), $target, $objectNotif, $collection);
                    }
                }

                //self::addActionHistory( $userId , $id, $collection, $action);
                
                self::updateParent( $id, $collection);

                //We update the points of the user
                if(isset($user['gamification']['actions'][$action])){
                    Gamification::incrementUser($userId, $action);
                }
                else{
                    Gamification::updateUser($userId);
                }

                //Moderate automatic 
                if($collection == Comment::COLLECTION && $action == "reportAbuse"){
                    $element = ($id) ? PHDB::findOne ($collection, array("_id" => new MongoId($id) )) : null;
                    if(isset($element[$action."Count"]) && $element[$action."Count"] >= 3){
                        PHDB::update($collection, array("_id" => new MongoId($element["_id"])), 
                                                                            array('$set' => array( "isAnAbuse" => true, "status"=>"declaredAbused"))
                        );
                    }
                }
               

                $res = array( "result"          => true,  
                              "userActionSaved" => true,
                              "user"            => PHDB::findOne ( Person::COLLECTION , array("_id" => new MongoId( $userId ) ),array("actions")),
                              "element"         => PHDB::findOne ($collection,array("_id" => new MongoId($id) ),array( $action)),
                              "inc"         => $inc,
                              "msg"             => "Ok !"
                               );
            } else {
                $res = array( "result" => true,  "userAllreadyDidAction" => true, "msg" => Yii::t("common","You have already made this action" ));
            }
        }
        return $res;
    }

    /* TODO BOUBOULE - Not necessary anymore ... ?
    The Action History colelction helps build timeline and historical visualisations 
    on a given item
    in time we could also use it as a base for undoing tasks
     */
    public static function addActionHistory($userId=null , $id=null, $collection=null, $action=null){
        $currentAction = array( "who"=> $userId,
                                "self" => $action,
                                "collection" => $collection,
                                "objectId" => $id,
                                "created"=>time()
                                );
        PHDB::insert( ActivityStream::COLLECTION, $currentAction );
    }
    
    /*
    update the updated date on a parent entity
     */
    public static function updateParent($id=null, $collection=null)
    {
        $updatableParentTypes = array(
            ActionRoom::TYPE_ACTIONS    => array("parentCollection" => ActionRoom::COLLECTION,
                                                 "parentField"=>"room"),
            Survey::COLLECTION          => array("parentCollection" => ActionRoom::COLLECTION,
                                                 "parentField"=>"survey"),
        );
        if( $obj = @$updatableParentTypes[$collection] )
        {
            $element = ($id) ? PHDB::findOne ($collection, array("_id" => new MongoId($id) )) : null;
            if( isset($element) && $parentId = @$element[ $obj["parentField"] ] ) 
            {
                PHDB::update ( $obj["parentCollection"], array("_id" => new MongoId( $parentId )), 
                                           array( '$set'=>array( "updated" => time())
                                                  ));
            }
        }
    }
    /**
   * check if loggued in user is in the "follow" field array for an entry
   * @return Boolean
   */
    public static function isUserFollowing( $value, $actionType )
    {
        //return ( isset($value[ $actionType ]) && is_array($value[ $actionType ]) && in_array(Yii::app()->session["userId"], $value[ $actionType ]) );
        $userId = Yii::app()->session["userId"];
        return ( isset($value[ $actionType ]) && 
                 is_array($value[ $actionType ]) && 
                (isset($value[ $actionType ][$userId]) || in_array(Yii::app()->session["userId"], $value[ $actionType ])) 
               );
    }

    /**
   * return an html according to enttry voting state
   * the total count of votes
   * filtering class
   * boolean hasVoted
   * @return array
   */
    public static function  voteLinksAndInfos( $logguedAndValid, $value )
    {
        $res = array( "links"=>"",
                      "totalVote"=>0,
                      "avoter" => "mesvotes",
                      "hasVoted" => true);
        //has loged user voted on this entry 
        //vote UPS
        $voteUpActive = ( $logguedAndValid && Action::isUserFollowing($value,Action::ACTION_VOTE_UP) ) ? "active":"";
        $voteUpCount = (isset($value[Action::ACTION_VOTE_UP."Count"])) ? $value[Action::ACTION_VOTE_UP."Count"] : 0 ;
        $hrefUp = ($logguedAndValid && empty($voteUpActive)) ? "javascript:addaction('".$value["_id"]."','".Action::ACTION_VOTE_UP."')" : "";
        $classUp = $voteUpActive." ".Action::ACTION_VOTE_UP." ".$value["_id"].Action::ACTION_VOTE_UP;
        $iconUp = ' fa-thumbs-up ';

        //vote ABSTAIN 
        $voteAbstainActive = ($logguedAndValid && Action::isUserFollowing($value,Action::ACTION_VOTE_ABSTAIN) ) ? "active":"";
        $voteAbstainCount = (isset($value[Action::ACTION_VOTE_ABSTAIN."Count"])) ? $value[Action::ACTION_VOTE_ABSTAIN."Count"] : 0 ;
        $hrefAbstain = ($logguedAndValid && empty($voteAbstainActive)) ? "javascript:addaction('".(string)$value["_id"]."','".Action::ACTION_VOTE_ABSTAIN."')" : "";
        $classAbstain = $voteAbstainActive." ".Action::ACTION_VOTE_ABSTAIN." ".$value["_id"].Action::ACTION_VOTE_ABSTAIN;
        $iconAbstain = ' fa-circle';

        //vote UNCLEAR
        $voteUnclearActive = ( $logguedAndValid && Action::isUserFollowing($value,Action::ACTION_VOTE_UNCLEAR) ) ? "active":"";
        $voteUnclearCount = (isset($value[Action::ACTION_VOTE_UNCLEAR."Count"])) ? $value[Action::ACTION_VOTE_UNCLEAR."Count"] : 0 ;
        $hrefUnclear = ($logguedAndValid && empty($voteUnclearCount)) ? "javascript:addaction('".$value["_id"]."','".Action::ACTION_VOTE_UNCLEAR."')" : "";
        $classUnclear = $voteUnclearActive." ".Action::ACTION_VOTE_UNCLEAR." ".$value["_id"].Action::ACTION_VOTE_UNCLEAR;
        $iconUnclear = " fa-pencil";

        //vote MORE INFO
        $voteMoreInfoActive = ( $logguedAndValid && Action::isUserFollowing($value,Action::ACTION_VOTE_MOREINFO) ) ? "active":"";
        $voteMoreInfoCount = (isset($value[Action::ACTION_VOTE_MOREINFO."Count"])) ? $value[Action::ACTION_VOTE_MOREINFO."Count"] : 0 ;
        $hrefMoreInfo = ($logguedAndValid && empty($voteMoreInfoCount)) ? "javascript:addaction('".$value["_id"]."','".Action::ACTION_VOTE_MOREINFO."')" : "";
        $classMoreInfo = $voteMoreInfoActive." ".Action::ACTION_VOTE_MOREINFO." ".$value["_id"].Action::ACTION_VOTE_MOREINFO;
        $iconMoreInfo = " fa-question-circle";

        //vote DOWN 
        $voteDownActive = ($logguedAndValid && Action::isUserFollowing($value,Action::ACTION_VOTE_DOWN) ) ? "active":"";
        $voteDownCount = (isset($value[Action::ACTION_VOTE_DOWN."Count"])) ? $value[Action::ACTION_VOTE_DOWN."Count"] : 0 ;
        $hrefDown = ($logguedAndValid && empty($voteDownActive)) ? "javascript:addaction('".(string)$value["_id"]."','".Action::ACTION_VOTE_DOWN."')" : "";
        $classDown = $voteDownActive." ".Action::ACTION_VOTE_DOWN." ".$value["_id"].Action::ACTION_VOTE_DOWN;
        $iconDown = " fa-thumbs-down";

        //votes cannot be changed, link become spans
        if( !empty($voteUpActive) || !empty($voteAbstainActive) || !empty($voteDownActive) || !empty($voteUnclearActive) || !empty($voteMoreInfoActive))
        {
            $linkVoteUp = ($logguedAndValid && !empty($voteUpActive) ) ? 
                            "<span class='".$classUp." ' ><i class='fa fa-caret-bottom'></i> ".
                                Yii::t("survey","Voted", null, Yii::app()->controller->module->id).
                                " <span class='btnvote color-btnvote-green'><i class='fa $iconUp' ></i> Pour</span></span>" : "";
            $linkVoteAbstain = ($logguedAndValid && !empty($voteAbstainActive)) ? 
                            "<span class='".$classAbstain." '><i class='fa fa-caret-bottom'></i> ".
                                Yii::t("survey","Voted", null, Yii::app()->controller->module->id).
                                " <span class='btnvote color-btnvote-white'><i class='fa $iconAbstain'></i> Blanc</span></span>" : "";
            $linkVoteUnclear = ($logguedAndValid && !empty($voteUnclearActive)) ? 
                            "<span class='".$classUnclear." '><i class='fa fa-caret-bottom'></i> ".
                                Yii::t("survey","Voted", null, Yii::app()->controller->module->id).
                                " <span class='btnvote color-btnvote-blue'><i class='fa  $iconUnclear'></i> Incompris</span></span>" : "";
            $linkVoteMoreInfo = ($logguedAndValid && !empty($voteMoreInfoActive)) ? 
                            "<span class='".$classMoreInfo." '><i class='fa fa-caret-bottom'></i> ".
                                Yii::t("survey","Voted", null, Yii::app()->controller->module->id).
                                " <span class='btnvote color-btnvote-purple'><i class='fa  $iconMoreInfo'></i> Incomplet</span></span>" : "";
            $linkVoteDown = ($logguedAndValid && !empty($voteDownActive)) ? 
                            "<span class='".$classDown." '><i class='fa fa-caret-bottom'></i> ".
                                Yii::t("survey","Voted", null, Yii::app()->controller->module->id).
                                " <span class='btnvote color-btnvote-red'><i class='fa $iconDown'></i> Contre</span></span>" : "";
        }
        else
        {
            $res["avoter"] = "avoter";
            $res["hasVoted"] = false;
            
            $linkVoteUp = ($logguedAndValid  ) ? "<a class='btn ".$classUp." voteIcon' data-vote='".Action::ACTION_VOTE_UP."' href=\" ".$hrefUp." \" title='Voter Pour'><i class='fa $iconUp' ></i></a>" : "";
            $linkVoteAbstain = ($logguedAndValid ) ? "<a class='btn ".$classAbstain." voteIcon'  data-vote='".Action::ACTION_VOTE_ABSTAIN."' href=\"".$hrefAbstain."\" title='Voter Blanc'><i class='fa $iconAbstain'></i></a>" : "";
            $linkVoteUnclear = ($logguedAndValid ) ? "<a class='btn ".$classUnclear." voteIcon' data-vote='".Action::ACTION_VOTE_UNCLEAR."' href=\"".$hrefUnclear."\" title='Voter Pas Clair, Pas fini, Amender'><i class='fa $iconUnclear'></i></a>" : "";
            $linkVoteMoreInfo = ($logguedAndValid ) ? "<a class='btn ".$classMoreInfo." voteIcon' data-vote='".Action::ACTION_VOTE_MOREINFO."' href=\"".$hrefMoreInfo."\" title=\"Voter Pour Plus d'informations\"><i class='fa $iconMoreInfo'></i></a>" : "";
            $linkVoteDown = ($logguedAndValid) ? "<a class='btn ".$classDown." voteIcon' data-vote='".Action::ACTION_VOTE_DOWN."' href=\"".$hrefDown."\" title='Voter Contre'><i class='fa $iconDown'></i></a>" : "";
        }

        //default Values are hasn't voted
        $res["totalVote"] = $voteUpCount+$voteAbstainCount+$voteDownCount+$voteUnclearCount+$voteMoreInfoCount;
        $res["ordre"] = $voteUpCount+$voteDownCount;
        $res["links"] = ( $value["type"] == Survey::TYPE_ENTRY ) ? "<span class='text-bold active btnvote color-btnvote-red'><i class='fa fa-clock-o'></i> ".Yii::t("survey","You did not vote", null, Yii::app()->controller->module->id)."</span>" : "";

        //$res["links"] = ($res["totalVote"]) ? "<span class='text-red text-bold'>RESULT</span>" : $res["links"];
        if( ($value["type"]==Survey::TYPE_ENTRY 
                && ( !isset($value["dateEnd"]) || $value["dateEnd"] > time() ) 
            ) || ($res["hasVoted"])
          )
            $res["links"] = "<div class='leftlinks'>".$linkVoteUp." ".$linkVoteUnclear." ".$linkVoteAbstain." ".$linkVoteMoreInfo." ".$linkVoteDown."</div>";
        else
            $res["avoter"] = "closed";
        
        return $res;
    }

}