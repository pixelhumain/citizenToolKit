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
                if( isset($_POST['urls']) && count($_POST['urls']) )
                    $entryInfos['urls'] = $_POST['urls'];

                $entryInfos['created'] = time();
                //specific application routines
                if( isset( $_POST["app"] ) )
                {
                    $appKey = $_POST["app"];
                    if($app = PHDB::findOne (PHType::TYPE_APPLICATIONS,  array( "key"=> $appKey ) ))
                    {
                        //when registration is done for an application it must be registered
                    	$entryInfos['applications'] = array( $appKey => array( "usertype"=> (isset($_POST['type']) ) ? $_POST['type']:$_POST['app']  ));
                        //check for application specifics defined in DBs application entry
                    	if( isset( $app["moderation"] ) ){
                    		$entryInfos['applications'][$appKey][SurveyType::STATUS_CLEARED] = false;
                            //TODO : set a Notification for admin moderation 
                        }
                        $res['applicationExist'] = true;
                    }else
                        $res['applicationExist'] = false;
                }

                $result = PHDB::updateWithOptions( Survey::COLLECTION,  array( "name" => $name ), 
                                                   array('$set' => $entryInfos ) ,
                                                   array('upsert' => true ) );
                
                $surveyId = PHDB::getIdFromUpsertResult($result);
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