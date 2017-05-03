<?php

class AboutAction extends CAction {
	public function run($type, $id, $view=null, $networkParams=null) { 
		$controller=$this->getController();

		$element=Element::getByTypeAndId($type,$id);
		$params["element"] = $element;
		$params["type"] = $type;
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		$params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$element["preferences"]);

		if(@Yii::app()->session["network"]){
			$params["openEdition"] = false;
			$params["edit"] = false;
		}

		$page = "about";
		$params["params"] = $params;
		if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial($page,$params,true);
		else 
			$controller->render( $page , $params );
	}
}
