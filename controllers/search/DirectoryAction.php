<?php
class DirectoryAction extends CAction
{
	public function run() {
		
		$controller=$this->getController();
		$controller->layout = "//layouts/mainSearch";
        $controller->renderPartial( "directory" );

		//return Rest::json(array("result" => true, "list" => $search));
	}
}