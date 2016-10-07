<?php

class Survey
{
	const COLLECTION = "surveys";
	const PARENT_COLLECTION = "actionRooms";
	const CONTROLLER = "survey";

	const TYPE_SURVEY = 'survey';
	const TYPE_ENTRY  = 'entry';
  	const STATUS_CLEARED 	= "cleared";
  	const STATUS_REFUSED 	= "refused";

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
		     	$survey = PHDB::findOne( self::COLLECTION, array("_id"=>new MongoId($params["survey"])) );
		     	if( isset($survey["applications"][$params["app"]]["cleared"] ))
		     	{
		     		if($params["action"]){
		     			PHDB::update( self::COLLECTION, 
		     									array("_id"=>new MongoId($params["survey"])),
		     									array('$unset' => array('applications.'.$params["app"].'.cleared' => true))
		     								);
		     			$res["msg"] = "EntryCleared";
		     			$res["result"] = true;
		     		} else {
		     			PHDB::update(  self::COLLECTION, 
		     								    array("_id"=>new MongoId($params["survey"])),
		     									array('$set' => array('applications.'.$params["app"].'.cleared' => "refused"))
		     								);
		     			$res["msg"] = "EntryRefused";
		     		}
		     	} else 
		     		$res["msg"] = "Nothing to clear on this entry";
		     	

		     	$res["survey"] = PHDB::findOne( self::COLLECTION, array("_id"=>new MongoId($params["survey"])) );
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
     		if( $survey = PHDB::findOne( self::COLLECTION, array("_id"=>new MongoId($params["survey"])) ) ) 
     		{
	     		if(Person::isAppAdmin( Yii::app()->session["userId"] , $params["app"] ))
	     		{
			     	
	     			//first remove all children 
			     	$count = PHDB::count( self::COLLECTION , array("survey" => $params["survey"]) );
			     	if( $count > 0){
				     	PHDB::remove( self::COLLECTION, array("survey"=>$params["survey"]));
				     	$res["msg2"] = "Deleted ".$count." children entries" ;
					}

			     	//then remove the parent survey
	     			PHDB::remove( self::COLLECTION,array("_id"=>new MongoId($params["survey"])));
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

     public static function getChartBarResult($survey){

      $voteDownCount      = (isset($survey[Action::ACTION_VOTE_DOWN."Count"])) ? $survey[Action::ACTION_VOTE_DOWN."Count"] : 0;
      $voteAbstainCount   = (isset($survey[Action::ACTION_VOTE_ABSTAIN."Count"])) ? $survey[Action::ACTION_VOTE_ABSTAIN."Count"] : 0;
      $voteUnclearCount   = (isset($survey[Action::ACTION_VOTE_UNCLEAR."Count"])) ? $survey[Action::ACTION_VOTE_UNCLEAR."Count"] : 0;
      $voteMoreInfoCount  = (isset($survey[Action::ACTION_VOTE_MOREINFO."Count"])) ? $survey[Action::ACTION_VOTE_MOREINFO."Count"] : 0;
      $voteUpCount        = (isset($survey[Action::ACTION_VOTE_UP."Count"])) ? $survey[Action::ACTION_VOTE_UP."Count"] : 0;
      
      $totalVotes = $voteDownCount+$voteAbstainCount+$voteUpCount+$voteUnclearCount+$voteMoreInfoCount;
      
      $oneVote = ($totalVotes!=0) ? 100/$totalVotes:1;
      
      $percentVoteDownCount     = round($voteDownCount    * $oneVote);
      $percentVoteAbstainCount  = round($voteAbstainCount * $oneVote);
      $percentVoteUpCount       = round($voteUpCount      * $oneVote);
      $percentVoteUnclearCount  = round($voteUnclearCount * $oneVote);
      $percentVoteMoreInfoCount = round($voteMoreInfoCount * $oneVote);

  //     	$percentVoteUpCount = 10;
		// $percentVoteUnclearCount = 30;
		// $percentVoteAbstainCount = 40;
		// $percentVoteMoreInfoCount = 20;
		// $percentVoteDownCount = 0;
		
		// $voteUpCount = 1;
		// $voteUnclearCount = 5;
		// $voteAbstainCount = 4;
		// $voteMoreInfoCount = 2;
		// $voteDownCount = 0;

      $html = "";

      $percentNoVote = "0";
      if($totalVotes == 0) $percentNoVote = "100";

      if($totalVotes > 1) $msgVote = "votes exprimés";
      else                $msgVote = "vote exprimé"; 

      $html .=  "<div class='col-md-12 no-padding'>".

                  "<div class='pull-left text-dark' style='margin-top:5px; margin-left:5px; font-size:13px;'>".
                      $totalVotes." ".$msgVote.
                  "</div>".

                  "<div class='space1'></div>";
        
      $html .=    '<div class="progress">'.
                    '<div class="progress-bar progress-bar-green progress-bar-striped" style="width: '.$percentVoteUpCount.'%">'.
                      $voteUpCount.' <i class="fa fa-thumbs-up"></i> ('.$percentVoteUpCount.'%)'.
                    '</div>'.
                    '<div class="progress-bar progress-bar-blue progress-bar-striped" style="width: '.$percentVoteUnclearCount.'%">'.
                      $voteUnclearCount.' <i class="fa fa-pencil"></i> ('.$percentVoteUnclearCount.'%)'.
                    '</div>'.
                    '<div class="progress-bar progress-bar-white progress-bar-striped" style="width: '.$percentVoteAbstainCount.'%">'.
                      $voteAbstainCount.' <i class="fa fa-circle"></i> ('.$percentVoteAbstainCount.'%)'.
                    '</div>'.
                    '<div class="progress-bar progress-bar-purple progress-bar-striped" style="width: '.$percentVoteMoreInfoCount.'%">'.
                      $voteMoreInfoCount.' <i class="fa fa-question-circle"></i> ('.$percentVoteMoreInfoCount.'%)'.
                    '</div>'.
                    '<div class="progress-bar progress-bar-red progress-bar-striped" style="width: '.$percentVoteDownCount.'%">'.
                      $voteDownCount.' <i class="fa fa-thumbs-down"></i> ('.$percentVoteDownCount.'%)'.
                    '</div>'.
                    '<div class="progress-bar progress-bar-white progress-bar-striped" style="width: '.$percentNoVote.'%">'.
                     // $percentNoVote.' '.
                    '</div>'.
                  '</div>'.
                '</div>'; 

      return $html;
    }


    public static function getChartCircle($survey, $voteLinksAndInfos, $parentType,$parentId){
    	$voteDownCount      = (isset($survey[Action::ACTION_VOTE_DOWN."Count"])) ? $survey[Action::ACTION_VOTE_DOWN."Count"] : 0;
		$voteAbstainCount   = (isset($survey[Action::ACTION_VOTE_ABSTAIN."Count"])) ? $survey[Action::ACTION_VOTE_ABSTAIN."Count"] : 0;
		$voteUnclearCount   = (isset($survey[Action::ACTION_VOTE_UNCLEAR."Count"])) ? $survey[Action::ACTION_VOTE_UNCLEAR."Count"] : 0;
		$voteMoreInfoCount  = (isset($survey[Action::ACTION_VOTE_MOREINFO."Count"])) ? $survey[Action::ACTION_VOTE_MOREINFO."Count"] : 0;
		$voteUpCount        = (isset($survey[Action::ACTION_VOTE_UP."Count"])) ? $survey[Action::ACTION_VOTE_UP."Count"] : 0;

		$totalVotes = $voteDownCount+$voteAbstainCount+$voteUpCount+$voteUnclearCount+$voteMoreInfoCount;

		$oneVote = ($totalVotes!=0) ? 100/$totalVotes:1;

		$percentVoteDown     = $voteDownCount    * $oneVote;
		$percentVoteAbstain  = $voteAbstainCount * $oneVote;
		$percentVoteUp       = $voteUpCount      * $oneVote;
		$percentVoteUnclear  = $voteUnclearCount * $oneVote;
		$percentVoteMoreInfo = $voteMoreInfoCount * $oneVote;


		// $percentVoteUp = 40;
		// $percentVoteUnclear = 25;
		// $percentVoteAbstain = 35;
		// $percentVoteMoreInfo = 65;
		// $percentVoteDown = 15;

		$actionUp 		= "javascript:addaction('".(string)$survey["_id"]."','".Action::ACTION_VOTE_UP."')";
		$actionAbstain 	= "javascript:addaction('".(string)$survey["_id"]."','".Action::ACTION_VOTE_ABSTAIN."')";
		$actionUnclear 	= "javascript:addaction('".(string)$survey["_id"]."','".Action::ACTION_VOTE_UNCLEAR."')";
		$actionMoreInfo = "javascript:addaction('".(string)$survey["_id"]."','".Action::ACTION_VOTE_MOREINFO."')";
		$actionDown 	= "javascript:addaction('".(string)$survey["_id"]."','".Action::ACTION_VOTE_DOWN."')";
		
		$hasVoted = $voteLinksAndInfos["hasVoted"];
		$isAuth =  Authorisation::canParticipate(Yii::app()->session['userId'],$parentType,$parentId);

		$hasVoted = !$isAuth || $hasVoted;

		$html = '<div class="col-md-1"></div>';
		$html .= self::getOneChartCircle($percentVoteUp, 		$voteUpCount,		$actionUp, 		"Pour", 	"green", 	"thumbs-up", 		$hasVoted);
		$html .= self::getOneChartCircle($percentVoteMoreInfo,  	$voteMoreInfoCount,	$actionMoreInfo,"Incomplet","blue", 	"pencil", 			$hasVoted);
		$html .= self::getOneChartCircle($percentVoteAbstain,	$voteAbstainCount,	$actionAbstain, "Blanc", 	"white", 	"circle", 			$hasVoted);
		$html .= self::getOneChartCircle($percentVoteUnclear, 	$voteUnclearCount,	$actionUnclear, "Incompris","purple", 	"question-circle", 	$hasVoted);
		$html .= self::getOneChartCircle($percentVoteDown, 		$voteDownCount,		$actionDown, 	"Contre", 	"red", 		"thumbs-down", 		$hasVoted);
		$html .= '<div class="col-md-1"></div>';
		if(!$isAuth)
			$html .= '<div class="col-md-12 center text-red"><br/> Vous n&apos;avez pas les droits pour voter. </div>';
		return $html;
	}

	private static function getOneChartCircle($percent, $voteCount, $action, $label, $color, $icon, $hasVoted){
		$colorTxt = ($color=="white") ? "black" : $color;
		$colXS = ($color=="white") ? "col-xs-12" : "col-xs-6";

		$tooltips = array("green"=>"Je suis favorable à cette proposition",
						"blue"=>"Je pense que cette proposition n'est pas complète",
						"white"=>"Je n'ai pas d'avis",
						"purple"=>"Je n'ai pas compris, il manque des informations",
						"red"=>"Je suis contre à cette proposition",
						);
		
		$tooltip = $tooltips[$color];

		$html = '<div class="col-md-2 col-sm-2 '.$colXS.' center">
		  		  <div class="col-md-12 no-padding">
		  			<div class="c100 p'.$percent.' '.$color.' small center">
					  <span>'.$percent.'%</span>
					  <div class="slice"> <div class="bar"></div> <div class="fill"></div>
					</div>
				  </div>
				  <div class="col-md-12 no-padding">
		  			<h4 class="text-'.$colorTxt.' bold"><i class="fa fa-'.$icon.'"></i> '.$label.' ('.$voteCount.')</h4>'.
		  		  '</div>'.
		  		'</div>';

		if(!$hasVoted)
		$html .=	'<button onclick="'.$action.'" data-original-title="'.$tooltip.'" data-toggle="tooltip" data-placement="bottom" '.
							'class="btn btn-default tooltips btn-sm text-'.$colorTxt.'"><i class="fa fa-gavel"></i> Voter</button>';
		
		$html .=  '</div>';
		
		return $html;
	}

}
?>