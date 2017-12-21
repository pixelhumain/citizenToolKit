<?php
class SaveTaskAction extends CAction
{
    public function run(){
    	$controller=$this->getController();
    	if($_POST)
    	$res=Gantt::saveTask($_POST);
    	return Rest::json( $res );
    }
}
