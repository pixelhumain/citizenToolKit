<?php

class SourceAdminAction extends CAction
{
    public function run()
    {
    	
        $controller=$this->getController();
        $params = array();
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("sourceadmin",$params,true);
        else 
            $controller->render("sourceadmin",$params);  
        
    }
}

?>