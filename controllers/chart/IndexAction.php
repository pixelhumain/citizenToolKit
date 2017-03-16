<?php
class IndexAction extends CAction
{
    public function run($type=null, $id=null, $chart=null){

    	$controller=$this->getController();
		$element = Element::getByTypeAndId($type,$id);
		$params["element"] = $element;
		$params["properties"]=$element["properties"]["chart"][$chart];
		$params["parentType"] = $type;
		$params["parentId"] = $id;
		$params["chartKey"] = $chart;
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		$params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$element["preferences"]);
        if(Yii::app()->request->isAjaxRequest){
			echo $controller->renderPartial("index", $params, true);

		}
    }
}