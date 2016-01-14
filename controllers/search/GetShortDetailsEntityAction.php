<?php
class GetShortDetailsEntityAction extends CAction
{
	public function run() {
		
		$id = isset($_POST["id"]) ? $_POST["id"] : "";
		$type = isset($_POST["type"]) ? $_POST["type"] : "";

		if($id == "" || $type == "") return Rest::json(array("result" => false, "msg" => "Error : id or type undefined"));

		$entity = null;
		if($type == "person") $entity = Person::getById($id);
		if($type == "organization") $entity = Organization::getById($id);
		if($type == "project") $entity = Project::getById($id);
		if($type == "event") $entity = Event::getById($id);

		if($entity == null) return Rest::json(array("result" => false, "msg" => "Error : no entity found"));
		
		//return Rest::json(array("result" => true, "msg" => "OK"));
		
		$controller=$this->getController();
		$controller->renderPartial( "shortDetailsEntity", array("entity" => $entity) );

		//return Rest::json(array("result" => true, "list" => $search));
	}
}