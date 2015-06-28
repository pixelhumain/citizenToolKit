<?php
class EntryAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();
      $where = array("survey"=>$id);
      $survey = PHDB::findOne (Action::ACTION_ROOMS, array("_id"=>new MongoId ( $id ) ) );
      $where["survey"] = $survey;
      
      $controller->title = "Sondages : ".$survey["name"];
      $controller->subTitle = "Décision démocratiquement simple";
      $controller->pageTitle = "Communecter - Sondages";

      Rest::json( array( 
        "title" => $survey["name"] ,
        "content" => $controller->renderPartial( "entry", array("survey"=>$survey), true),
        "contentBrut" => $survey["message"] ) 
      );
    }
}