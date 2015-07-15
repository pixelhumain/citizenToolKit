<?php

class OpenDataAction extends CAction
{
    public function run($insee,$typeData="population", $type=null)
    {
    	
        $controller=$this->getController();
         $params["insee"] = $insee;
        $controller->render("openData",$params);
    }
}