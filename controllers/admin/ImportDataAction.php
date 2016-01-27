<?php

class ImportDataAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
    	$params = Import::importData($_FILES, $_POST);

        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("importData",$params,true);
        else 
            $controller->render("importData",$params);
    }
}

?>