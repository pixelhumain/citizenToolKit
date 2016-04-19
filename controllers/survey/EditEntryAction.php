<?php
class EditEntryAction extends CAction
{
    public function run( $survey,$id=null )
    {
        $controller=$this->getController();
        $surveyObj = PHDB::findOne (Survey::PARENT_COLLECTION, array("_id"=>new MongoId ( $survey ) ) );
        if($id)
        {
            $entry = PHDB::findOne (Survey::COLLECTION, array("_id"=>new MongoId ( $id ) ) );
            $params = array( 
                "title" => $entry["name"] ,
                "content" => $controller->renderPartial( "entry", array( "survey" => $entry ), true),
                "contentBrut" => $entry["message"],
                "survey" => $entry,
                 ) ;

          if( isset($entry["organizerType"]) )
          {
              if( $entry["organizerType"] == Person::COLLECTION ){
                $organizer = Person::getById( $entry["organizerId"] );
                $params["organizer"] = array(  "name" => $organizer["name"],
                                               "link" => Yii::app()->createUrl('/'.$controller->module->id."/person/dashboard/id/".$entry["organizerId"]) );
              }
              else if( $entry["organizerType"] == Organization::COLLECTION ){
                $organizer = Organization::getById( $entry["organizerId"] );
                $params["organizer"] = array(  "name" => $organizer["name"],
                                               "link" => Yii::app()->createUrl('/'.$controller->module->id."/organization/dashboard/id/".$entry["organizerId"]) );
              }
          }

          //Images
          $contentKeyBase = Yii::app()->controller->id.".".Yii::app()->controller->action->id;
          $limit = array(Document::IMG_PROFIL => 1);
          $images = Document::getListDocumentsURLByContentKey($id, $contentKeyBase, Document::DOC_TYPE_IMAGE, $limit);
          $params["images"] = $images;
          $params["contentKeyBase"] = $contentKeyBase;
        }
        $params = array( "survey" => $surveyObj );
        echo $controller->renderPartial("editEntrySV" , $params,true);
    }
}