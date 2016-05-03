<?php
/**
 * [actionAddWatcher 
 * create or update a user account
 * if the email doesn't exist creates a new citizens with corresponding data 
 * else simply adds the watcher app the users profile ]
 * @return [json] 
 */
class SaveSessionAction extends CAction
{
    public function run()
    {
        error_log("saveSession");
        $res = array();
        if( Yii::app()->session["userId"] )
        {
            $email = $_POST["email"];
            $name  = $_POST['name'];

            //Organizer of the survey
            if ($_POST['organizer'] == "currentUser") {
                $organizerId = Yii::app()->session["userId"];
                $organizerType = Person::COLLECTION;
            } else {
                $organizerId = $_POST['organizer'];
                $organizerType = Organization::COLLECTION;
            }

            //if exists login else create the new user
            //TODO Tib : do not use the email to retrieve a person : prefere use the getById
            if(PHDB::findOne (Person::COLLECTION, array( "email" => $email ) ))
            {
                //udate the new app specific fields
                $entryInfos = array();
                $entryInfos['email'] = (string)$email;
                $entryInfos['name'] = (string)$name;
                $entryInfos['organizerId'] = $organizerId;
                $entryInfos['organizerType'] = $organizerType;
                if( isset($_POST['survey']) ){
                    $entryInfos['survey'] = $_POST['survey'];
                    $res['parentId'] = $_POST['survey'];
                    //this might not be necessary , since the information is on the parent survey
                    $surveyRoom = PHDB::findOne (Survey::PARENT_COLLECTION, array( "_id" => new MongoId($_POST['survey']) ) );
                    if( isset( $surveyRoom["parentType"] ) && isset($_POST['parentType']) ) 
                        $entryInfos['parentType'] = $_POST['parentType'];
                    if( isset( $surveyRoom["parentId"] ) && isset($_POST['parentId']) ) 
                        $entryInfos['parentId'] = $_POST['parentId'];
                        
                }
                if( isset($_POST['message']) )
                    $entryInfos['message'] = (string)$_POST['message'];
                if( isset($_POST['type']) )
                    $entryInfos['type'] = $_POST['type'];
                if( isset($_POST['tags']) && count($_POST['tags']) )
                    $entryInfos['tags'] = $_POST['tags'];
                if( isset($_POST['cp']) )
                    $entryInfos['cp'] = explode(",",$_POST['cp']);
                if( isset($_POST['urls']) && count($_POST['urls'])>0 )
                    $entryInfos['urls'] = $_POST['urls'];
                if( isset($_POST['dateEnd']) && $_POST['dateEnd'] != "" )
                    $entryInfos['dateEnd'] = strtotime( str_replace("/", "-", $_POST['dateEnd']) );

                $entryInfos['created'] = time();
                

                $where = array();
                if( isset( $_POST['id'] ) ){
                    $where["_id"] = new MongoId($_POST['id']);
                    $result = PHDB::update( Survey::COLLECTION,  $where, 
                                                   array('$set' => $entryInfos ));
                    $surveyId = $_POST['id'];
                } else {
                    $surveyId = new MongoId();
                    $entryInfos["_id"] = $surveyId;
                    $result = PHDB::insert( Survey::COLLECTION,$entryInfos );
                }
                
                /*$result = PHDB::updateWithOptions( Survey::COLLECTION,  $where, 
                                                   array('$set' => $entryInfos ) ,
                                                   array('upsert' => true ) ); */
                //Save the comment options
                if (@$_POST["commentOptions"]) {
                    Comment::saveCommentOptions( $surveyId ,Survey::COLLECTION, $_POST["commentOptions"]);
                }

                $res['result'] = true;
                $res['msg'] = "surveySaved";
                $res['surveyId'] = $surveyId;
                
            } else
                $res = array('result' => false , 'msg'=>"user doen't exist");
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
            
        Rest::json($res);  
        Yii::app()->end();
    }
}
