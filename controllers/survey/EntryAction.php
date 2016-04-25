<?php
class EntryAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();
      $survey = PHDB::findOne (Survey::COLLECTION, array("_id"=>new MongoId ( $id ) ) );
     
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
                                           "link" => "loadByHash('#person.detail.id.".$survey["organizerId"]."')");
          }
          else if( $survey["organizerType"] == Organization::COLLECTION ){
            $organizer = Organization::getById( $survey["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => "loadByHash('#organization.detail.id.".$survey["organizerId"]."')");
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
        $controller->layout = "//layouts/mainSearch";
        $controller->render( "entryStandalone", $params );
      } else 
          Rest::json( $params);
    }
}