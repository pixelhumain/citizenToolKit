<?php

class AboutAction extends CAction {
	public function run($type, $id, $view=null, $networkParams=null) { 
		$controller=$this->getController();

		$element=Element::getByTypeAndId($type,$id);
		
		if(@$element["parentId"] && @$element["parentType"])
            $element['parent'] = Element::getByTypeAndId( $element["parentType"], $element["parentId"]);

        if(@$element["organizerId"] && @$element["organizerType"] && 
            $element["organizerId"] != "dontKnow" && $element["organizerType"] != "dontKnow")
            $element['organizer'] = Element::getByTypeAndId( $element["organizerType"], $element["organizerId"]);

		if(@Yii::app()->session["network"]){
			$params["openEdition"] = false;
			$params["edit"] = false;
		}
		
		$params["element"] = $element;
		$params["type"] = $type;
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		$params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$element["preferences"]);
		$params["params"] = $params;

		$page = "about";
		if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial($page,$params,true);
		else 
			$controller->render( $page , $params );
	}
}
