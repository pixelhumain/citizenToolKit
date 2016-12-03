<?php

class CreateFileAction extends CAction
{
    public function run()
    {
        $controller = $this->getController();
        $count = PHDB::count(NewImport::MAPPINGS, array() ) ;
        if($count == 0){
        	NewImport::initMappings();	
        }

    	$params["allMappings"] = NewImport::getMappings();
    	if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("createFile",$params,true);
        else 
            $controller->render("createFile",$params);
    }
}

?>