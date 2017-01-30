<?php
class CheckGeoCodageAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
    	$params = array();
    	
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("checkgeocodage",$params,true);
        else 
            $controller->render("checkgeocodage",$params);
    }
}

?>