<?php

class CreateGraphAction extends CAction{
    
    public function run($insee){
        $controller=$this->getController();

        $params = array("insee" => $insee);

        $controller->title = ((!empty($name)) ? $name : "City : ".$insee)."'s Directory";
        $controller->subTitle = (isset($city["description"])) ? $city["description"] : "";
        $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;
        $controller->render("createGraph",$params);
    }
    	
}

?>
