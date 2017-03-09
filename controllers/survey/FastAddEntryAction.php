<?php
/**
 * [actionAddWatcher 
 * create or update a user account
 * if the email doesn't exist creates a new citizens with corresponding data 
 * else simply adds the watcher app the users profile ]
 * @return [json] 
 */
class FastAddEntryAction extends CAction
{
    public function run( $room=null )
    {
        $res = array();
        if( Yii::app()->session["userId"] )
        {
            //if coming from a selection in a discussion room 
            //if the action room doesn't exist
            //or if multiple actions come from the same discussion room
            $canAdd = null;
            //Organizer of the survey
            $organizerId = Yii::app()->session["userId"];
            $organizerType = Person::COLLECTION;
            if( @$_POST['discussionId'] )
            {
                $parentRoom = PHDB::findOne ( ActionRoom::COLLECTION, array( "copyOf" => $_POST['discussionId'],"type"=>ActionRoom::TYPE_SURVEY ) );
                if(!@$parentRoom) {
                    //if corresponding actions rooms doesn't exist yet
                    $parentRoom = PHDB::findOne ( ActionRoom::COLLECTION, array("_id"=>new MongoId ( $_POST['discussionId']) ) );
                    $canAdd = Authorisation::canParticipate($organizerId, $parentRoom['parentType'], $parentRoom['parentId']);
                    if( $canAdd )
                        $parentRoom = ActionRoom::insert($parentRoom,ActionRoom::TYPE_VOTE,$_POST['discussionId']);
                }
            } else
                $parentRoom = PHDB::findOne ( ActionRoom::COLLECTION, array( "room" => $room ) );

            

            //if exists login else create the new user
            //TODO Tib : do not use the email to retrieve a person : prefere use the getById
            if(!@$canAdd)
                $canAdd = Authorisation::canParticipate($organizerId, $parentRoom['parentType'], $parentRoom['parentId']);
            if( $canAdd )
            {
                //udate the new app specific fields
                $txt = $_POST['txt'];
                $title = $_POST['txt'];
                if( strlen( $title ) > 60 )
                    $title = substr($_POST['txt'], 0 , 60)."...";
                
                $entryInfos = array();
                $entryInfos['email'] = Yii::app()->session["userEmail"];
                $entryInfos['name'] = $title;
                $entryInfos['organizerId'] = $organizerId;
                $entryInfos['organizerType'] = $organizerType;
                $entryInfos['survey'] = (string)$parentRoom["_id"];
                $entryInfos['parentType'] = $parentRoom['parentType'];
                $entryInfos['parentId'] = (string)$parentRoom["_id"];
                $entryInfos['message'] = Yii::t("rooms","Copied from a discussion :",null,Yii::app()->controller->module->id)."<br/>".$txt;
                $entryInfos['type'] = Survey::TYPE_ENTRY;
                $entryInfos['dateEnd'] = time()+(10*24*60*60); //10j plus tard
                $entryInfos['created'] = time();
                
                $entryInfos["_id"] = new MongoId();
                PHDB::insert( Survey::COLLECTION,$entryInfos );
                
                $res['result'] = true;
                $res['msg'] = "Proposition bien enregistré";
                $res['surveyId'] = $entryInfos["_id"];
                $res['hash'] = "#survey.entry.id.".$entryInfos["_id"];

                //Notify Element participants 
                Notification::actionOnPerson ( ActStr::VERB_ADD_PROPOSAL, ActStr::ICON_ADD, "", array( "type" => Survey::COLLECTION , "id" => $entryInfos["_id"] ));
                
            } else
                $res = array('result' => false , 'msg'=>"user doen't exist");
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
            
        Rest::json($res);  
        Yii::app()->end();
    }
}
