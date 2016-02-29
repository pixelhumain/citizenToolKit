<?php

class AddDataAction extends CAction
{
    public function run() {
        $controller = $this->getController();
    	$params = array();

        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("adddata",$params,true);
        else 
            $controller->render("adddata",$params);
    }
}

?>