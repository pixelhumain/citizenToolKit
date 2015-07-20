<?php

class IndexAction extends CAction
{
    public function run($insee=null, $type=null)
    {
    	
        $controller=$this->getController();

        /*$city = PHDB::findOne( City::COLLECTION , array( "insee" => $insee ) );
        $name = (isset($city["name"])) ? $city["name"] : "";*/
        $controller->title = "Admin ".Yii::app()->session[ "userIsAdmin" ]."";
        $controller->subTitle = "Managing the system";
        $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

        $controller->toolbarMBZ = array("<li id='linkBtns'><a href='".Yii::app()->createUrl("/".$controller->module->id."/admin/directory")."' ><i class='fa fa-align-justify'></i>DIRECTORY</a></li>");

        $params["insee"] = $insee;
        $params["type"] = $type;
        $controller->render("index",$params);
    }
}