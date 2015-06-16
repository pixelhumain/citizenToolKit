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
    	} else
        	echo json_encode(array("result"=>false, "msg"=>"Ce projet doit avoir un nom."));
    exit;

	}
}