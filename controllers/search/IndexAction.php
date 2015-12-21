<?php
class IndexAction extends CAction
{
	public function run() {
		
		$controller=$this->getController();
		$controller->layout = "//layouts/mainSearch";
        $controller->render( "index" );

		//return Rest::json(array("result" => true, "list" => $search));
	}
}