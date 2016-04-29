<?php

class IndexAction extends CAction
{
    public function run($key)
    {
    	$controller=$this->getController();
        $page = "../error/error";
        if(Role::isSourceAdmin(Role::getRolesUserId(Yii::app()->session["userId"]))){
            if(Person::getSourceAdmin(Yii::app()->session["userId"])){
                $params["entitiesSourceAdmin"] = Import::getAllEntitiesByKey($key);
                $page = "index";
            }
        }
        
        if(Yii::app()->request->isAjaxRequest)
                echo $controller->renderPartial("index",$params,true);
            else 
                $controller->render("index",$params);
         
        
    }
}