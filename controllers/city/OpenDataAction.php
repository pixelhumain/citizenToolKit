<?php

class OpenDataAction extends CAction
{
    public function run($insee,$typeData="population", $type=null)
    {
    	
        $controller=$this->getController();

        $city = PHDB::findOne( City::COLLECTION , array( "insee" => $insee ) );
        $name = (isset($city["name"])) ? $city["name"] : "";
        $controller->title = ( (!empty($name)) ? $name : "City : ".$insee)."'s Directory";
        $controller->subTitle = (isset($city["description"])) ? $city["description"] : "";
        $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

        $params["insee"] = $insee;
        $params["city"] = $city;
        $page = "openData";
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
        else 
        $controller->render($page, $params );
    }
}