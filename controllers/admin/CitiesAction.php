<?php

class CitiesAction extends CAction
{
    public function run() {
        $controller = $this->getController();

    	$params = array();
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("cities",$params,true);
        else 
            $controller->render("cities",$params);
    }
}

?>