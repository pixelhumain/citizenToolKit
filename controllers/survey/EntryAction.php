<?php
class EntryAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();
      $where = array("survey"=>$id);
      $survey = PHDB::findOne (Survey::COLLECTION, array("_id"=>new MongoId ( $id ) ) );
      $where["survey"] = $survey;
      
      $controller->title = "Sondages : ".$survey["name"];
      $controller->subTitle = "Décision démocratiquement simple";
      $controller->pageTitle = "Communecter - Sondages : ".$survey["name"];

      $pageView = ActivityStream::getWhere(array("verb"=>ActStr::VERB_VIEW,
                                                 "author.ip"=>$_SERVER['REMOTE_ADDR'],
                                                 "object.objectType" => ActStr::TYPE_URL,
                                                 "object.id"=>$controller->id."/".$controller->action->id."/id/".$id));

      if( !count($pageView) )
      {
        ActStr::viewPage( $controller->id."/".$controller->action->id."/id/".$id );
        //incerment this survey's entry pageView
        PHDB::update ( Survey::COLLECTION , array("_id" => new MongoId($id)) , array('$inc'=>array( "viewCount" => 1 ) ));
      }


      $params = array( 
            "title" => $survey["name"] ,
            "content" => $controller->renderPartial( "entry", array( "survey" => $survey ), true),
            "contentBrut" => $survey["message"],
            "survey" => $survey,
             ) ;

      if( isset($survey["organizerType"]) )
      {
          if( $survey["organizerType"] == Person::COLLECTION ){
            $organizer = Person::getById( $survey["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => Yii::app()->createUrl('/'.$controller->module->id."/person/dashboard/id/".$survey["organizerId"]) );
          }
          else if( $survey["organizerType"] == Organization::COLLECTION ){
            $organizer = Organization::getById( $survey["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => Yii::app()->createUrl('/'.$controller->module->id."/organization/dashboard/id/".$survey["organizerId"]) );
          }
      }

      //Images
      $contentKeyBase = Yii::app()->controller->id.".".Yii::app()->controller->action->id;
      $limit = array(Document::IMG_PROFIL => 1);
      $images = Document::getListDocumentsURLByContentKey($id, $contentKeyBase, Document::DOC_TYPE_IMAGE, $limit);
      $params["images"] = $images;
      $params["contentKeyBase"] = $contentKeyBase;

      if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("entryStandalone",$params,true);
      else if( !Yii::app()->request->isAjaxRequest ){
          $controller->layout = "//layouts/mainSimple";
          $controller->renderPartial( "entryStandalone", $params );
      } else 
          Rest::json( $params);
    }
}