<?php
class EditActionAction extends CAction
{
    public function run( $room,$id=null )
    {
        $controller=$this->getController();
        
        $params = array( 
          "parentRoom" => PHDB::findOne (ActionRoom::COLLECTION, array("_id"=>new MongoId ( $room ) ) ),
          "parentRoomId" => $room
        );

        if($id)
        {
            $entry = PHDB::findOne (ActionRoom::COLLECTION_ACTIONS, array("_id"=>new MongoId ( $id ) ) );
            
            //TKA BUG : organizerId can be an organisation 
            //we need a person
            //to test with organization as organizer  
            if($entry['organizerId'] != Yii::app()->session["userId"] )
              return array('result' => false , 'msg'=>'Access Denied');

            $params ["action"] = $entry;
                 
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
        }
        //var_dump($params);
        echo $controller->renderPartial("editAction" , $params,true);
    }
}