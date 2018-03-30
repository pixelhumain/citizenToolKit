<?php

class AboutAction extends CAction {
	public function run($type, $id, $view=null, $networkParams=null) { 
		$controller=$this->getController();

		$element=Element::getByTypeAndId($type,$id);
		
		if(@$element["parentId"] && @$element["parentType"]&& 
            $element["parentId"] != "dontKnow" && $element["parentType"] != "dontKnow")
            $element['parent'] = Element::getByTypeAndId( $element["parentType"], $element["parentId"]);

        if(@$element["organizerId"] && @$element["organizerType"] && 
            $element["organizerId"] != "dontKnow" && $element["organizerType"] != "dontKnow")
            $element['organizer'] = Element::getByTypeAndId( $element["organizerType"], $element["organizerId"]);

		if(@Yii::app()->session["network"]){
			$params["openEdition"] = false;
			$params["edit"] = false;
		}
		if($type==Event::COLLECTION)
			$params["typesList"]=Event::$types;
		else if($type==Organization::COLLECTION)
			$params["typesList"]=Organization::$types;
		$params["element"] = $element;
		$params["type"] = $type;
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		$params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$element["preferences"]);
		$params["params"] = $params;
		if(Yii::app()->params["CO2DomainName"] == "terla")
			$page="../element/terla/about";
		else
			$page = "about";
		if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial($page,$params,true);
		else 
			$controller->render( $page , $params );
	}
}
