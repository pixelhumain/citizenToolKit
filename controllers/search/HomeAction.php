<?php
class HomeAction extends CAction
{
	public function run() {
		
		$controller=$this->getController();
		$controller->layout = "//layouts/mainSearch";
        $controller->render( "home" );
        
		//return Rest::json(array("result" => true, "list" => $search));
	}
}