<?php
class ContactAction extends CAction
{
	

    public function run()
    {
    	$controller=$this->getController();
    	$controller->title = "Importer vos contacts";
        $controller->subTitle = "";
        $controller->pageTitle = "Importer vos contacts";
    	$controller->render("contact");
    }
}