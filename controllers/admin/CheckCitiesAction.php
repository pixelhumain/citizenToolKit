<?php
class CheckCitiesAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();

        
        //$params['cities'] = json_encode(City::getCitiesForcheck());
        $params['cities'] = array();
    	if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("checkcities",$params,true);
        else 
            $controller->render("checkcities",$params);
    }
}

?>