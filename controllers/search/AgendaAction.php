<?php
class AgendaAction extends CAction
{
	public function run() {
		
		$controller=$this->getController();
		$controller->layout = "//layouts/mainSearch";
        $controller->renderPartial( "agenda" );
        
		//return Rest::json(array("result" => true, "list" => $search));
	}
}