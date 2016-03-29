<?php
class DefaultAction extends CAction {

	public function run() 
	{
		$controller=$this->getController();
        $controller->pageTitle = "TEST";
        $controller->layout = "//layouts/mainDirectory";
        $controller->render("simplyDirectory");
	}
}