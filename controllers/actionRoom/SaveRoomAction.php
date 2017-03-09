<?php
/**
 * @return [json] 
 */
class SaveRoomAction extends CAction
{
    public function run()
    {
        $res = array();
        if( Yii::app()->session["userId"] && @$_POST['name'] )
        {
            $email = $_POST["email"];
            $name  = $_POST['name'];

            //check if owner of the proposal exists login else create the new user
            if(PHDB::findOne (Person::COLLECTION, array( "email" => $email ) ))
            {
                //udate the new app specific fields
                $newInfos = array();
                $newInfos['email'] = (string)$email;
                $newInfos['name'] = (string)$name;
                $newInfos['type'] = $_POST['type'];
                if( @$_POST["type"] == ActionRoom::TYPE_FRAMAPAD ) 
                    $newInfos['url'] = "https://annuel.framapad.org/p/".InflectorHelper::slugify( $newInfos['name'] );

                if( @$_POST["parentType"] ) 
                    $newInfos['parentType'] = $_POST['parentType'];
                if( @$_POST["parentId"] ) 
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
                if( @$_POST['tags'] && count($_POST['tags']) )
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
                    array(  "type"=>@$newInfos['parentType'] ? $newInfos['parentType'] : "",
                            "id"=> @$newInfos['parentId'] ? $newInfos['parentId'] : ""), 
                    array("id"=>(string)$newInfos["_id"],"type"=> ActionRoom::COLLECTION), 
                    $newInfos['type']
                );
                /*Notification::actionOnPerson ( ActStr::VERB_ADDROOM, ActStr::ICON_ADD, "", 
                                                array( "type" => ActionRoom::COLLECTION , 
                                                       "id" => (string)$newInfos["_id"], 
                                                       "parentId" => @$newInfos['parentId'] ? $newInfos['parentId'] : "", 
                                                       "parentType" => @$newInfos['parentType'] ? $newInfos['parentType'] : "", 
                                                       "name" => (string)$name ));*/
            }else
                $res = array('result' => false , 'msg'=>"user doen't exist");
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
            
        Rest::json($res);  
        Yii::app()->end();
    }
}