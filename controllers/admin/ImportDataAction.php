<?php

class ImportDataAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
    	$params = array();

        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("importData",$params,true);
        else 
            $controller->render("importData",$params);
    }
}