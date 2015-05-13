<?php
class RemoveAndBacktractAction extends CAction {
	
	/**
	* delete a document
	* @param $id id of the document that we want to delete
	*/
	public function run() {
		$result = array("result"=>false,"msg"=>"Vos données n'ont pas pu être modifiées");
		if(isset($_POST["_id"])){
			Document::removeDocumentById($_POST["_id"]);
			Document::setImagePath($_POST["id"], $_POST["type"], $_POST["imagePath"], $_POST["contentKey"]);
			$result = array("result"=>true,"msg"=>"Vos données ont bien été modifiées");
		}
		return Rest::json($result);
	}

}