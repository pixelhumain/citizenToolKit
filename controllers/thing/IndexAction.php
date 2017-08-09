<?php

/**
* @author: Danzal
* Date: 26/01/2017
* Modified : 30/05/2017
*/

class IndexAction extends CAction
{
    public function run()
    {
        $controller=$this->getController();
        $params = array();
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("index",$params,true);
        else 
            $controller->render("index",$params);
    }
}