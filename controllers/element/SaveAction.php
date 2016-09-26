<?php

class SaveAction extends CAction {
/**
* Dashboard Organization
*/
    public function run() { 

        if(Yii::app()->request->isAjaxRequest && isset(Yii::app()->session["userId"]))
        {
            //var_dump($_POST);
            $id = null;
            $data = null;
            $collection = $_POST["collection"];
            if( !empty($_POST["id"]) ){
                $id = $_POST["id"];
            }
            $key = $_POST["key"];


            /*unset($_POST['id']);
            if( $_POST['collection'] == PHType::TYPE_MICROFORMATS){
                $_POST['collection'] = $_POST['MFcollection'];
                unset( $_POST['MFcollection'] );
            } else {
                unset($_POST['collection']);
                unset($_POST['key']);
            }


            //empty fields aren't properly validated and must be removed
            foreach ($_POST as $key => $value) {
                if($value == "")
                    unset($_POST[$key]);
            }

            $microformat = PHDB::findOne(PHType::TYPE_MICROFORMATS, array( "key"=> $key));
            $validate = ( !isset($microformat )  || !isset($microformat["jsonSchema"])) ? false : true;
            //validation process based on microformat defeinition of the form
            //by default dont perform validation test
            $valid = array("result"=>true);
            if($validate)
                $valid = PHDB::validate( $key, json_decode (json_encode ($_POST), FALSE) );
            

            if( $valid["result"] )*/

            
            unset($_POST['collection']);
            unset($_POST['key']);


            //empty fields aren't properly validated and must be removed
            foreach ($_POST as $k => $v) {
                if($v== "")
                    unset($_POST[$k]);
            }

            /*$microformat = PHDB::findOne(PHType::TYPE_MICROFORMATS, array( "key"=> $key));
            $validate = ( !isset($microformat )  || !isset($microformat["jsonSchema"])) ? false : true;
            //validation process based on microformat defeinition of the form
            */
            //validation process based on databind on each Elemnt Model
            
            $valid = DataValidator::validate( ucfirst($key), json_decode (json_encode ($_POST)) );
            
            if( $valid )
            {
                if($id)
                {
                    //update a single field
                    //else update whole map
                    $changeMap = ( !$microformat && isset( $key )) ? array('$set' => array( $key => $_POST[ $key ] ) ) : array('$set' => $_POST );
                    PHDB::update($collection,array("_id"=>new MongoId($id)), $changeMap);
                    $res = array("result"=>true,
                                 "msg"=>"Vos données ont été mise à jour.",
                                 "reload"=>true,
                                 "map"=>$_POST,
                                 "id"=>(string)$_POST["_id"]);
                } 
                else 
                {
                    $_POST["created"] = time();
                    PHDB::insert($collection, $_POST );
                    $res = array("result"=>true,
                                 "msg"=>"Vos données ont bien été enregistré.",
                                 "reload"=>true,
                                 "map"=>$_POST,
                                 "id"=>(string)$_POST["_id"]);
                }
            } else 
                $res = array( "result" => false, 
                              "msg" => Yii::t("common","Something went really bad : Invalid Content") );

            echo json_encode( $res );  
        }
    }
}

?>