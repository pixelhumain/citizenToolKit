<?php
class EditEntryAction extends CAction
{
    public function run( $survey,$id=null )
    {
        $controller=$this->getController();
        $parentSurvey = PHDB::findOne (Survey::PARENT_COLLECTION, array("_id"=>new MongoId ( $survey ) ) );
        $params = array( "parentSurvey" => $parentSurvey );
        if($id)
        {
            $entry = PHDB::findOne (Survey::COLLECTION, array("_id"=>new MongoId ( $id ) ) );
            
            //TKA BUG : organizerId can be an organisation 
            //we need a person
            //to test with organization as organizer  
            if($entry['organizerId'] != Yii::app()->session["userId"] )
              return array('result' => false , 'msg'=>'Access Denied');

            $params ["title"] = $entry["name"];
            $params ["content"] = $controller->renderPartial( "entry", array( "survey" => $entry ), true);
            $params ["contentBrut"] = $entry["message"];
            $params ["survey"] = $entry;
                 

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
        echo $controller->renderPartial("editEntrySV" , $params,true);
    }
}