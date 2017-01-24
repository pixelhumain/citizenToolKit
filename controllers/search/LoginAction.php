<?php
class LoginAction extends CAction
{
	public function run() {
		
		$controller=$this->getController();
		$controller->layout = "//layouts/mainSearch";
        $controller->render( "login" );
        
		//return Rest::json(array("result" => true, "list" => $search));
	}
}