<?php

class CircuitsAction extends CAction
{
    public function run() {
        $controller = $this->getController();
		$params=array();
    	//$params = City::getAllCities();
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("terla/circuits",$params,true);
        else 
            $controller->render("terla/circuits",$params);
    }
}

?>