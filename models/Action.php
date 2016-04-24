<?php
/*
- actions are saved on any needed element in any collection

 */
class Action
{
    const NODE_ACTIONS          = "actions";

    const ACTION_ROOMS          = "actionRooms";
    const ACTION_ROOMS_TYPE_SURVEY = "survey";

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
        public static function addAction( $userId=null , $id=null, $collection=null, $action=null, $unset=false, $multiple=false, $reason="")
    {
        $user = Person::getById($userId);
        $element = ($id) ? PHDB::findOne ($collection, array("_id" => new MongoId($id) )) : null;
        $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
        
        if($user && $element)
        {
            //check user hasn't allready done the action
            if( $unset 
                || !isset( $element[ $action ] ) 
                || ( !$multiple && isset( $element[ $action ] ) && !in_array( (string)$user["_id"] , $element[ $action ] )
                || $multiple ) )
            {
                if($unset)
                    $dbMethod = '$unset';
                else
                    $dbMethod = '$set';

                // "actions": { "groups": { "538c5918f6b95c800400083f": { "voted": "voteUp" }, "538cb7f5f6b95c80040018b1": { "voted": "voteUp" } } } }
                if (!empty($reason))
	                $addToMap = $reason ; 
                else 
                	$addToMap = $action;
                $map[ self::NODE_ACTIONS.".".$collection.".".(string)$element["_id"].".".$action ] = $addToMap ;

                //update the user table 
                //adds or removes an action
                PHDB::update ( Person::COLLECTION , array( "_id" => $user["_id"]), 
                                                    array( $dbMethod => $map));
                if($unset){
                    $dbMethod = '$pull';
                    //decrement when removing an action instance
                    $inc = -1;
                }
                else 
                {
                    //push unique user Ids into action node list
                    $dbMethod = '$addToSet';
                    //increment according to specifications
                    $inc = 1;
                }
                
                if(isset($reason)){
                    PHDB::update ($collection, array("_id" => new MongoId($element["_id"])), 
                                           array( $dbMethod => array( 
                                                    $action => (string)$user["_id"],
                                                    $action."Reason" => array((string)$user["_id"] => $reason)),
                                                    '$inc'=>array( $action."Count" => $inc)));
                }
                else{
                    PHDB::update ($collection, array("_id" => new MongoId($element["_id"])), 
                                           array( $dbMethod => array( 
                                                    $action => (string)$user["_id"]),
                                                    '$inc'=>array( $action."Count" => $inc)));
                }
                
                self::addActionHistory( $userId , $id, $collection, $action);
                
                $res = array( "result"          => true,  
                              "userActionSaved" => true,
                              "user"            => PHDB::findOne ( Person::COLLECTION , array("_id" => new MongoId( $userId ) ),array("actions")),
                              "element"         => PHDB::findOne ($collection,array("_id" => new MongoId($id) ),array( $action)),
                              "inc"			=> $inc,
                              "msg"             => "Ok !"
                               );
            } else 
                $res = array( "result" => true,  "userAllreadyDidAction" => true, "msg" => Yii::t("common","You have already made this action" ));
        }
        return $res;
    }

    /*
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
    
    /**
   * check if loggued in user is in the "follow" field array for an entry
   * @return Boolean
   */
    public static function isUserFollowing( $value, $actionType )
    {
        return ( isset($value[ $actionType ]) && is_array($value[ $actionType ]) && in_array(Yii::app()->session["userId"], $value[ $actionType ]) );
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
                                " <br><span class='btnvote color-btnvote-green'><i class='fa $iconUp' ></i> favorable</span></span>" : "";
            $linkVoteAbstain = ($logguedAndValid && !empty($voteAbstainActive)) ? 
                            "<span class='".$classAbstain." '><i class='fa fa-caret-bottom'></i> ".
                                Yii::t("survey","Voted", null, Yii::app()->controller->module->id).
                                " <br><span class='btnvote color-btnvote-white'><i class='fa $iconAbstain'></i> blanc</span></span>" : "";
            $linkVoteUnclear = ($logguedAndValid && !empty($voteUnclearActive)) ? 
                            "<span class='".$classUnclear." '><i class='fa fa-caret-bottom'></i> ".
                                Yii::t("survey","Voted", null, Yii::app()->controller->module->id).
                                " <br><span class='btnvote color-btnvote-yellow'><i class='fa  $iconUnclear'></i> à terminer</span></span>" : "";
            $linkVoteMoreInfo = ($logguedAndValid && !empty($voteMoreInfoActive)) ? 
                            "<span class='".$classMoreInfo." '><i class='fa fa-caret-bottom'></i> ".
                                Yii::t("survey","Voted", null, Yii::app()->controller->module->id).
                                " <br><span class='btnvote color-btnvote-purple'><i class='fa  $iconMoreInfo'></i> + d'infos</span></span>" : "";
            $linkVoteDown = ($logguedAndValid && !empty($voteDownActive)) ? 
                            "<span class='".$classDown." '><i class='fa fa-caret-bottom'></i> ".
                                Yii::t("survey","Voted", null, Yii::app()->controller->module->id).
                                " <br><span class='btnvote color-btnvote-red'><i class='fa $iconDown'></i> défavorable</span></span>" : "";
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

        $res["totalVote"] = $voteUpCount+$voteAbstainCount+$voteDownCount+$voteUnclearCount+$voteMoreInfoCount;
        $res["ordre"] = $voteUpCount+$voteDownCount;
        $res["links"] = ($value["type"]==Survey::TYPE_ENTRY) ? "<span class='text-red text-bold active'><i class='fa fa-caret-right'></i> ".Yii::t("survey","Closed", null, Yii::app()->controller->module->id)."</span>" : "";
        //$res["links"] = ($res["totalVote"]) ? "<span class='text-red text-bold'>RESULT</span>" : $res["links"];
        if($value["type"]==Survey::TYPE_ENTRY && (!isset($value["dateEnd"]) || $value["dateEnd"] > time() ) )
            $res["links"] = "<div class='leftlinks'>".$linkVoteUp." ".$linkVoteUnclear." ".$linkVoteAbstain." ".$linkVoteMoreInfo." ".$linkVoteDown."</div>";
        
        return $res;
    }

}