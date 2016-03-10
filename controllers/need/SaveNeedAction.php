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
                $context = array();
                $context["parentId"] = $_POST["parentId"];
                $context["parentType"] = $_POST["parentType"];

                if (isset($_POST["startDate"])){
	                $newNeed["startDate"] = $_POST["startDate"];
	                $newNeed["endDate"] = $_POST["endDate"];
                }                
                $newNeed['created'] = time();

                $res=Need::insert($newNeed,$context);
                //PHDB::insert( Survey::PARENT_COLLECTION, $newNeed );
                /*PHDB::updateWithOptions( Survey::PARENT_COLLECTION,  array( "name" => $name ), 
                                                   array('$set' => $newInfos ) ,
                                                   array('upsert' => true ) );
                */
                
                $res['result'] = true;
                $res['msg'] = "Need Saved";
                $res["newNeed"] = $newNeed;
          //  }else
            //    $res = array('result' => false , 'msg'=>"user doen't exist");
        } else
            $res = array('result' => false , 'msg'=>'something somewhere went terribly wrong');
            
        Rest::json($res);  
        Yii::app()->end();
	}
}