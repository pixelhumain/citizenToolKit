<?php
class PhotoVideoAction extends CAction
{
    public function run($id=null, $type, $insee=null)
    {
        $controller=$this->getController();
        $params = array();
		$params["type"] = $type;
		if(isset($id)){
			$params["itemId"] = $id;
			if(isset(Yii::app()->session["userId"]))
			$params["canEdit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		}else if (isset($insee)){
			$params["insee"] = $insee;
			$where = array("insee" => $insee);
			$city = City::getWhere($where);
			foreach ($city as $key => $value) {
				$id = $value["_id"];
			}
			$params["itemId"] = $id;
		}

		
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("photoVideo", $params,true);
	    else
	        $controller->render("photoVideo",$params);
    }
}