<?php
/**
 * @return [json] 
 */
class SaveSurveyAction extends CAction
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
                $newInfos['type'] = Survey::TYPE_SURVEY;
                if( isset( $_POST["parentType"] ) ) 
                    $newInfos['parentType'] = $_POST['parentType'];
                if( isset( $_POST["parentId"] ) ) 
                    $newInfos['parentId'] = $_POST['parentId'];

                //these fields are necessary for multi scoping search features
                if(@$_POST["parentType"] && @$_POST["parentId"]){
                    $parent = Element::getByTypeAndId(@$_POST["parentType"], @$_POST["parentId"]);
                    if(@$parent["address"])
                        $newInfos['address'] = $parent["address"];
                    if(@$parent["geo"])
                        $newInfos['geo'] = $parent["geo"];
                    if(@$parent["geoPosition"])
                        $newInfos['geoPosition'] = $parent["geoPosition"];
                }
                
                if( isset($_POST['tags']) && count($_POST['tags']) )
                    $newInfos['tags'] = $_POST['tags'];
                
                $newInfos['created'] = time();
                $newInfos['updated'] = time();
                $newInfos["modified"] = new MongoDate(time());
                PHDB::insert( Survey::PARENT_COLLECTION, $newInfos );
                /*PHDB::updateWithOptions( Survey::PARENT_COLLECTION,  array( "name" => $name ), 
                                                   array('$set' => $newInfos ) ,
                                                   array('upsert' => true ) );
                */
                $res['result'] = true;
                $res['msg'] = "survey Room Saved";
                $res["savingTo"] = Survey::PARENT_COLLECTION;
                $res["newInfos"] = $newInfos;

                //Notify Element participants 
                Notification::constructNotification(ActStr::VERB_ADD, 
                    array("id" => Yii::app()->session["userId"],"name"=> Yii::app()->session["user"]["name"]), 
                    array(  "type"=>Survey::COLLECTION,
                            "id"=> (string)$newInfos["_id"]), 
                    null, 
                    Survey::COLLECTION
                );
               // Notification::actionOnPerson ( ActStr::VERB_ADD_PROPOSAL, ActStr::ICON_ADD, "", array( "type" => Survey::COLLECTION , "id" => (string)$newInfos["_id"] ));
            }else
                $res = array('result' => false , 'msg'=>"user doen't exist");
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
            
        Rest::json($res);  
        Yii::app()->end();
    }
}