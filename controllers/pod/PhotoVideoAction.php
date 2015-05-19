<?php
class PhotoVideoAction extends CAction
{
    public function run($id, $type, $typeid)
    {
        $controller=$this->getController();
        $params = array();
		$params["type"] = $type;
		$params["itemId"] = $id;
		$params["typeid"] = $typeid;
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("photoVideo", $params,true);
	    else
	        $controller->render("photoVideo",$params);
    }
}