<?php
/**
 * FastAddActionAction
 * create action with a single piece of text
 * can come from a selected text or a modal text entry for fast adding of task 
 * @return [json] 
 */
class FastAddActionAction extends CAction
{
    public function run($room=null)
    {
        
        $res = array();
        if( Yii::app()->session["userId"] )
        {
            //if coming from a selection in a discussion room 
            //if the action room doesn't exist
            //or if multiple actions come from the same discussion room
            $canAdd = null;
            //Organizer of the action
            $organizerId = Yii::app()->session["userId"];
            $organizerType = Person::COLLECTION;
            if( @$_POST['discussionId'] )
            {
                $parentRoom = PHDB::findOne ( ActionRoom::COLLECTION, array( "copyOf" => $_POST['discussionId'],"type"=>ActionRoom::TYPE_ACTIONS ) );
                if(!@$parentRoom) {
                    //if corresponding actions rooms doesn't exist yet
                    $parentRoom = PHDB::findOne ( ActionRoom::COLLECTION, array("_id"=>new MongoId ( $_POST['discussionId']) ) );
                    $canAdd = Authorisation::canParticipate($organizerId, $parentRoom['parentType'], $parentRoom['parentId']);
                    if( $canAdd )
                        $parentRoom = ActionRoom::insert($parentRoom,ActionRoom::TYPE_ACTIONS,$_POST['discussionId']);
                }
            } else
                $parentRoom = PHDB::findOne ( ActionRoom::COLLECTION, array( "room" => $room ) );
            
            //check if user is Member of Element
            if(!@$canAdd)
                $canAdd = Authorisation::canParticipate($organizerId, $parentRoom['parentType'], $parentRoom['parentId']);
            if( $canAdd )
            {
                $txt = $_POST['txt'];
                $title = $_POST['txt'];
                if( strlen( $title ) > 60 )
                    $title = substr($_POST['txt'], 0 , 60)."...";
                //udate the new app specific fields
                $entryInfos = array();
                $entryInfos['email'] = Yii::app()->session["userEmail"];
                $entryInfos['name'] = $title;
                $entryInfos['organizerId'] = $organizerId;
                $entryInfos['organizerType'] = $organizerType;
                $entryInfos['room'] = (string)$parentRoom["_id"];
                $res['parentId'] = (string)$parentRoom["_id"];
                $entryInfos['parentType'] = $parentRoom['parentType'];
                $entryInfos['parentId'] = $parentRoom['parentId'];
                $entryInfos['message'] = Yii::t("rooms","Copied from a discussion :",null,Yii::app()->controller->module->id)."<br/>".$txt;
                $entryInfos['type'] = ActionRoom::TYPE_ACTION;
                $entryInfos['created'] = time();
                

                $actionId = new MongoId();
                $entryInfos["_id"] = $actionId;
                $result = PHDB::insert( ActionRoom::COLLECTION_ACTIONS,$entryInfos );
                

                $res['result'] = true;
                $res['msg'] = Yii::t("rooms","action Saved",null,Yii::app()->controller->module->id);
                $res['actionId'] = $actionId;
                $res['hash'] = "#rooms.action.id.".$actionId;

                //Notify Element participants 
                Notification::actionOnPerson ( ActStr::VERB_ADD_ACTION, ActStr::ICON_ADD, "", array( "type" => ActionRoom::COLLECTION_ACTIONS , "id" => $actionId ));
                
            } else
                $res = array('result' => false , 'msg'=>"user doen't exist, or cannot do this action");
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
            
        Rest::json($res);  
        Yii::app()->end();
    }
}
