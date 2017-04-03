<?php

class AboutAction extends CAction {
	public function run($type, $id, $view=null, $networkParams=null) { 
		$controller=$this->getController();
		/*

		$element=Element::getSimpleByTypeAndId($type,$id);
		$preferences=Element::getSimpleByTypeAndId($type,$id,array("preferences"));
		if($type==Project::COLLECTION){
			$avancement=Element::getSimpleByTypeAndId($type,$id,array("properties.avancement"));
			if(@$avancement["avancement"]) $params["avancement"]=$avancement["avancement"];
		}
		//$params["listTypes"] = $listsEvent["eventTypes"];
		$params["controller"] = Element::getControlerByCollection($type);
		$params["tags"] = array("TODO : écrire la liste de suggestion de tags"); Tags::getActiveTags();
		$params["element"] = $element;
		$params["type"] = $type;
		$params["preferences"]=$preferences["preferences"];
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		$params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$preferences["preferences"]);
		if(@Yii::app()->session["network"]){
			$params["openEdition"] = false;
			$params["edit"] = false;
		}
		$params["isLinked"] = Link::isLinked($id,$type, Yii::app()->session['userId'], @$element["links"]);
		if(@$_POST["modeEdit"]){
			$params["modeEdit"]=$_POST["modeEdit"];
		}
		*/

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
