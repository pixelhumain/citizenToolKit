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
            //if exists login else create the new user
            if(PHDB::findOne (Person::COLLECTION, array( "email" => $email ) ))
            {
                //udate the new app specific fields
                $newInfos = array();
                $newInfos['email'] = (string)$email;
                $newInfos['name'] = (string)$name;
                if( isset($_POST['survey']) ){
                    $newInfos['survey'] = $_POST['survey'];
                    //this might not be necessary , since the information is on the parent survey
                    /*$surveyRoom = PHDB::findOne (Survey::PARENT_COLLECTION, array( "_id" => new MongoId($_POST['survey']) ) );
                    if( isset( $surveyRoom["parentType"] ) && isset($_POST['parentType']) ) 
                        $newInfos['parentType'] = $_POST['parentType'];
                    if( isset( $surveyRoom["parentId"] ) && isset($_POST['parentId']) ) 
                        $newInfos['parentId'] = $_POST['parentId'];
                        */
                }
                if( isset($_POST['message']) )
                    $newInfos['message'] = (string)$_POST['message'];
                if( isset($_POST['type']) )
                    $newInfos['type'] = $_POST['type'];
                if( isset($_POST['tags']) && count($_POST['tags']) )
                    $newInfos['tags'] = $_POST['tags'];
                if( isset($_POST['cp']) )
                    $newInfos['cp'] = explode(",",$_POST['cp']);
                if( isset($_POST['urls']) && count($_POST['urls']) )
                    $newInfos['urls'] = $_POST['urls'];

                $newInfos['created'] = time();
                //specific application routines
                if( isset( $_POST["app"] ) )
                {
                    $appKey = $_POST["app"];
                    if($app = PHDB::findOne (PHType::TYPE_APPLICATIONS,  array( "key"=> $appKey ) ))
                    {
                        //when registration is done for an application it must be registered
                    	$newInfos['applications'] = array( $appKey => array( "usertype"=> (isset($_POST['type']) ) ? $_POST['type']:$_POST['app']  ));
                        //check for application specifics defined in DBs application entry
                    	if( isset( $app["moderation"] ) ){
                    		$newInfos['applications'][$appKey][SurveyType::STATUS_CLEARED] = false;
                            //TODO : set a Notification for admin moderation 
                        }
                        $res['applicationExist'] = true;
                    }else
                        $res['applicationExist'] = false;
                }

                PHDB::updateWithOptions( Survey::COLLECTION,  array( "name" => $name ), 
                                                   array('$set' => $newInfos ) ,
                                                   array('upsert' => true ) );
                
                $res['result'] = true;
                $res['msg'] = "surveySaved";
                
            }else
                $res = array('result' => false , 'msg'=>"user doen't exist");
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
            
        Rest::json($res);  
        Yii::app()->end();
    }
}