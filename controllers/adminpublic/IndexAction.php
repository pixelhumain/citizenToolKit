<?php

class IndexAction extends CAction
{
    public function run($key)
    {
    	$controller=$this->getController();
        $params["entitiesSourceAdmin"] = Import::getAllEntitiesByKey($key);
        //$params = array();
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("index",$params,true);
        else 
            $controller->render("index",$params); 
        
    }
}