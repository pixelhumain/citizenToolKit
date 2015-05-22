<?php
class PhotoVideoAction extends CAction
{
    public function run($id, $type)
    {
        $controller=$this->getController();
        $params = array();
		$params["type"] = $type;
		$params["itemId"] = $id;
		if(isset(Yii::app()->session["userId"]))
			$params["canEdit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("photoVideo", $params,true);
	    else
	        $controller->render("photoVideo",$params);
    }
}