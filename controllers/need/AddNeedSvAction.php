<?php
class AddNeedSvAction extends CAction
{
    public function run($id=null,$type=null){
        assert('!empty($_GET["id"]); //The id is mandatory');
        assert('!empty($_GET["type"]); //The type is mandatory');

    	$controller=$this->getController();
    	$params = array();
    	$params["type"]  = $type;
        if( $type == Project::COLLECTION )
            $params["element"]  = Project::getPublicData($id);
        else if( $type == Organization::COLLECTION )
            $params["element"]  = Organization::getPublicData($id);
        else {
            error_log("Invalid call to AddNeedSvAction ".$id."/".$type);
            echo Rest::json(array("error" => array("msg" => "Invalid call to AddNeedSvAction", "params" => array("id" => $id, "type" => $type))));
            exit;
        }
        $params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		$params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$params["element"]["preferences"]);
        //TODO : add a else to fallback and display error

        if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("addNeedSV", $params, true);
    }
}