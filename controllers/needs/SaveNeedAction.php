<?php
class SaveNeedAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $res = array();
        if( Yii::app()->session["userId"] )
        {
            //$email = $_POST["email"];
            //$name  = $_POST['name'];
            //if exists login else create the new user
            //if(PHDB::findOne (Person::COLLECTION, array( "email" => $email ) ))
            //{
                //udate the new app specific fields
                $newNeed = array();
                $newNeed['name'] = $_POST["name"];
                $newNeed['type'] = $_POST['type'];
                $newNeed['duration'] = $_POST["needIsPonctual"];
                $newNeed["quantity"] = $_POST["quantity"];
                $newNeed["benefits"] = $_POST["needIsRemunerate"];
                
                if (isset($_POST["startDate"])){
	                $newNeed["startDate"] = $_POST["startDate"];
	                $newNeed["endDate"] = $_POST["endDate"];
                }
                if( isset( $_POST["parentType"] ) ) 
                    $newNeed['parentType'] = $_POST['parentType'];
                if( isset( $_POST["parentId"] ) ) 
                    $newNeed['parentId'] = $_POST['parentId'];
                
                $newNeed['created'] = time();

                $res=Need::insert($newNeed);
               $url = Yii::app()->createUrl("/".$controller->module->id."/needs/dashboard/idNeed/".$res["idNeed"]."/type/".$newNeed['parentType']."/id/".$newNeed['parentId']."");

                //PHDB::insert( Survey::PARENT_COLLECTION, $newNeed );
                /*PHDB::updateWithOptions( Survey::PARENT_COLLECTION,  array( "name" => $name ), 
                                                   array('$set' => $newInfos ) ,
                                                   array('upsert' => true ) );
                */
                $res['result'] = true;
                $res['msg'] = "Need Saved";
                $res["url"] = $url;
                $res["newNeed"] = $newNeed;
          //  }else
            //    $res = array('result' => false , 'msg'=>"user doen't exist");
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
            
        Rest::json($res);  
        Yii::app()->end();
	}
}