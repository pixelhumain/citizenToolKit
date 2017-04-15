<?php

class IndexAction extends CAction
{
    public function run()
    {
    	
        /*$controller=$this->getController();
        $params = array();
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("admin/index",$params,true);
        else 
            $controller->render("index",$params);*/

        $controller = $this->getController();
        $params = array();
        $controller->renderPartial("index", $params);
        
    }
}