<?php
class HomeAction extends CAction
{
	public function run() {
		
		$controller=$this->getController();
		$controller->layout = "//layouts/mainSearch";

		$controller->renderPartial( "home" );
        
		//return Rest::json(array("result" => true, "list" => $search));
	}
}