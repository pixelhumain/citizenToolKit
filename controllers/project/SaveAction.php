<?php
class SaveAction extends CAction
{
    public function run() {
		$controller=$this->getController();
		if( isset($_POST['title']) && !empty($_POST['title']))
		{
        //TODO check by key
            $project = PHDB::findOne(PHType::TYPE_PROJECTS ,array( "name" => $_POST['title']));
            if(!$project)
            { 
               //validate isEmail
               $res = Project::saveProject($_POST);
               echo json_encode($res);
            } else
                   echo json_encode(array("result"=>false, "msg"=>"Ce projet existe déjà."));
<<<<<<< HEAD
    	} else
        	echo json_encode(array("result"=>false, "msg"=>"Ce projet doit avoir un nom."));
=======
    } else
        echo json_encode(array("result"=>false, "msg"=>Yii::t("common", "Uncorrect request")));
>>>>>>> 3e3619a3243147747239972d8930bf2c7455b784
    exit;

	}
}