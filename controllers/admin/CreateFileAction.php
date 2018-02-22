<?php

class CreateFileAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        $count = PHDB::count(Import::MAPPINGS, array() ) ;
        if($count == 0){
        	Import::initMappings();	
        }

    	$params["allMappings"] = Import::getMappings();
        var_dump($params);
    	if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("createFile",$params,true);
        else 
            $controller->render("createFile",$params);
    }
}

?>