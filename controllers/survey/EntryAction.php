<?php
class EntryAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();
      $where = array("survey"=>$id);
      $survey = PHDB::findOne (Survey::COLLECTION, array("_id"=>new MongoId ( $id ) ) );
      PHDB::update (Survey::COLLECTION, array("_id" => new MongoId($id)), array('$inc'=>array( "viewCount" => 1 )));
      $where["survey"] = $survey;
      
      $controller->title = "Sondages : ".$survey["name"];
      $controller->subTitle = "Décision démocratiquement simple";
      $controller->pageTitle = "Communecter - Sondages";

      $params = array( 
            "title" => $survey["name"] ,
            "content" => $controller->renderPartial( "entry", array( "survey" => $survey ), true),
            "contentBrut" => $survey["message"],
            "survey" => $survey ) ;
      
      if(!Yii::app()->request->isAjaxRequest){
          $controller->layout = "//layouts/mainSimple";
          $controller->render( "entryStandalone", $params );
      } else
          Rest::json( $params);
    }
}