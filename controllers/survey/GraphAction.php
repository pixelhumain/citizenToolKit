<?php
class GraphAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();
      
      $where = array("survey"=>$id);
      $survey = PHDB::findOne (Survey::COLLECTION, array("_id"=>new MongoId ( $id ) ) );
      $where["survey"] = $survey;
      $voteDownCount = (isset($survey[Action::ACTION_VOTE_DOWN."Count"])) ? $survey[Action::ACTION_VOTE_DOWN."Count"] : 0;
      $voteAbstainCount = (isset($survey[Action::ACTION_VOTE_ABSTAIN."Count"])) ? $survey[Action::ACTION_VOTE_ABSTAIN."Count"] : 0;
      $voteUnclearCount = (isset($survey[Action::ACTION_VOTE_UNCLEAR."Count"])) ? $survey[Action::ACTION_VOTE_UNCLEAR."Count"] : 0;
      $voteMoreInfoCount = (isset($survey[Action::ACTION_VOTE_MOREINFO."Count"])) ? $survey[Action::ACTION_VOTE_MOREINFO."Count"] : 0;
      $voteUpCount = (isset($survey[Action::ACTION_VOTE_UP."Count"])) ? $survey[Action::ACTION_VOTE_UP."Count"] : 0;
      $totalVotes = $voteDownCount+$voteAbstainCount+$voteUpCount+$voteUnclearCount+$voteMoreInfoCount;
      $oneVote = ($totalVotes!=0) ? 100/$totalVotes:1;
      $voteDownCount = $voteDownCount * $oneVote ;
      $voteAbstainCount = $voteAbstainCount * $oneVote;
      $voteUpCount = $voteUpCount * $oneVote;
      $voteUnclearCount = $voteUnclearCount * $oneVote;
      $voteMoreInfoCount = $voteMoreInfoCount * $oneVote;

      Rest::json( array( "title" => "Repartition de  votes : ".$survey["name"] ,
                         "content" => $controller->renderPartial( "graph", array( "name" => $survey["name"],
                                                                                  "voteDownCount" => $voteDownCount,
                                                                                  "voteAbstainCount" => $voteAbstainCount,
                                                                                  "voteUpCount" => $voteUpCount,
                                                                                  "voteUnclearCount" => $voteUnclearCount,
                                                                                  "voteMoreInfoCount" => $voteMoreInfoCount,
                                                                                ), 
                                                            true)));
    }
}