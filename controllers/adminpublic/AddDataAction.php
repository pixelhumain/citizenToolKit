<?php

class AddDataAction extends CAction
{
    public function run() {
        $controller = $this->getController();
    	$params = array();
    	//$city = SIG::getInseeByLatLngCp("48.380317", "-4.5084217", "29200");
    	//$params["city"] = json_encode($city) ;
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("adddata",$params,true);
        else 
            $controller->render("adddata",$params);
    }
}

?>