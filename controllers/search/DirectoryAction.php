<?php
class DirectoryAction extends CAction
{
	public function run() {
		
		$controller=$this->getController();
		$controller->layout = "//layouts/mainSearch";
        $controller->render( "directory" );

		//return Rest::json(array("result" => true, "list" => $search));
	}
}