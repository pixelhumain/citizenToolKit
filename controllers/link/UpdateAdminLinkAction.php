<?php
class UpdateAdminLinkAction extends CAction{
	public function run(){
		//Rest::json($_POST); exit ;
		$form = PHDB::findOne( $_POST["parentType"] , 
								array("_id"=> new MongoId($_POST["parentId"]) ), 
								array("links" ) );
		//Rest::json($form); exit ;
		if(!empty($form) && 
			!empty($form["links"]) && 
			!empty($form["links"][$_POST["connect"]][$_POST["childId"]])){
			if($_POST["isAdmin"] === true){
				$form["links"][ $_POST["connect"] ][ $_POST["childId"] ]["isAdmin"] = true ;
			}else{
				unset($form["links"][ $_POST["connect"] ][ $_POST["childId"] ]["isAdmin"]);
			}

			
			
			$res = PHDB::update( $_POST["parentType"], 
										array("_id" => new MongoId($_POST["parentId"])), 
	                          			array('$set' => array("links" => $form["links"])));
		}
		Rest::json($form); exit ;
	}
}