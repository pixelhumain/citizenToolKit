<?php

class ImportDataAction extends CAction
{
    public function run()
    {
    	$controller=$this->getController();
        $params = array();
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("importdata",$params,true);
        else 
            $controller->render("importdata",$params); 
        
    }
}
