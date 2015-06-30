<?php
class SaveTaskAction extends CAction
{
    public function run(){

    	$controller=$this->getController();
    	//print_r($_POST);
    	$res=Project::saveTask($_POST);
    	//$res=array("result"=>true, "msg"=>"The task has been added with success");
    	return Rest::json( $res );
    }
}