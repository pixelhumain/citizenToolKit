<?php
class AddNeedSvAction extends CAction
{
    public function run($id=null,$type=null){
        assert('!empty($_GET["id"]); //The id is mandatory');
        assert('!empty($_GET["type"]); //The type is mandatory');

    	$controller=$this->getController();
    	$params = array();
    	
        if( $type == Project::COLLECTION )
            $params["project"]  = Project::getPublicData($id);
        else if( $type == Organization::COLLECTION )
            $params["organization"]  = Organization::getPublicData($id);
        else {
            error_log("Invalid call to AddNeedSvAction ".$id."/".$type);
            echo Rest::json(array("error" => array("msg" => "Invalid call to AddNeedSvAction", "params" => array("id" => $id, "type" => $type))));
            exit;
        }
        //TODO : add a else to fallback and display error

        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("addNeedSV", $params, true);
    }
}