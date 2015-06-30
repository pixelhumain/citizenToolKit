<?php
class PhotoVideoAction extends CAction
{
    public function run($id=null, $type, $insee=null)
    {
        $controller=$this->getController();
        $params = array();
		$params["type"] = $type;
		if(isset($id)){
			$params["photoVidId"] = $id;
			if(isset(Yii::app()->session["userId"]))
			$params["canEdit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		}else if (isset($insee)){
			$params["insee"] = $insee;
			
			$params["photoVidId"] = City::getIdByInsee($insee);
		}

		
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("photoVideo", $params,true);
	    else
	        $controller->render("photoVideo",$params);
    }
}