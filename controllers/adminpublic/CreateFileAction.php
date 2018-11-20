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
        
        $userId = Yii::app()->session["userId"];
        $where = array("userId" => $userId);

        $params["allMappings"] = Import::getMappings($where);
        
    	if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial("createfile",$params,true);
        else 
            $controller->render("createfile",$params);
    }
}

?>