<?php

class DeleteLinkAction extends CAction {
    
    public function run() {
	    //assert('!empty($_POST["childType"])'); //The child type is mandatory');
	   	$set=array($_POST["connect"].".".$_POST["id"]=>true);    	
		PHDB::update(Poi::COLLECTION,array("_id"=>new MongoId($_POST["parentId"])),array('$unset'=>$set));
		$result = array("result"=>true, "msg" => "ok");
		
		Rest::json($result);
    }
}