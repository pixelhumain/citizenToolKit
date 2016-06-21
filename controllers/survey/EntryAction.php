<?php
class EntryAction extends CAction
{
    public function run($id)
    {
      $controller=$this->getController();
      $survey = PHDB::findOne (Survey::COLLECTION, array("_id"=>new MongoId ( $id ) ) );
     
      if(!isset($survey)){
        throw new CTKException("Impossible to find this survey entry");
      }

      $pageView = ActivityStream::getWhere(array("verb"=>ActStr::VERB_VIEW,
                                                 "ip"=>$_SERVER['REMOTE_ADDR'],
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
            //"content" => $controller->renderPartial( "entry", array( "survey" => $survey ), true),
            "contentBrut" => $survey["message"],
            "survey" => $survey,
             ) ;

      if( isset($survey["organizerType"]) )
      {
          if( $survey["organizerType"] == Person::COLLECTION )
          {
            $organizer = Person::getById( $survey["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => "loadByHash('#person.detail.id.".$survey["organizerId"]."')");
          }
          else if( $survey["organizerType"] == Organization::COLLECTION )
          {
            $organizer = Organization::getById( $survey["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => "loadByHash('#organization.detail.id.".$survey["organizerId"]."')");
          }
          else if( $survey["organizerType"] == Project::COLLECTION )
          {
            $organizer = Project::getById( $survey["organizerId"] );
            $params["organizer"] = array(  "name" => $organizer["name"],
                                           "link" => "loadByHash('#project.detail.id.".$survey["organizerId"]."')");
          }

          $params["organizerType"] = $survey["organizerType"];
      }

      if( isset($survey["survey"]) )
      { 
         $parentRoom = ActionRoom::getById( $survey["survey"] );
         $params["parentSpace"] = $parentRoom;

         if( $parentRoom["parentType"] == Person::COLLECTION )
          {
            $parent = Person::getById( $parentRoom["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                           "link" => "loadByHash('#person.detail.id.".$parentRoom["parentId"]."')");
          }
          else if( $parentRoom["parentType"] == Organization::COLLECTION )
          {
            $parent = Organization::getById( $parentRoom["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                           "link" => "loadByHash('#organization.detail.id.".$parentRoom["parentId"]."')");
          }
          else if( $parentRoom["parentType"] == Project::COLLECTION )
          {
            $parent = Project::getById( $parentRoom["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                           "link" => "loadByHash('#project.detail.id.".$parentRoom["parentId"]."')");
          }
          else if( $parentRoom["parentType"] == City::COLLECTION )
          {
            $parent = City::getByUnikey( $parentRoom["parentId"] );
            $params["parent"] = array(  "name" => $parent["name"],
                                        "insee" => $parent["insee"],
                                        "cp" => $parent["cp"],
                                        "link" => "loadByHash('#city.detail.insee.".$parent["insee"].".postalCode.".$parent["cp"]."')");
          }

          $params["parentType"] = $parentRoom["parentType"];
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