<?php
class AddChartSvAction extends CAction
{
    public function run($type=null, $id=null){

    	$controller=$this->getController();
		$element = Element::getByTypeAndId($type,$id);
		$params["element"] = $element;
		$params["properties"]=array();
		if (isset($element["properties"]["chart"])){
				$params["properties"]=$element["properties"]["chart"];
			}
		$params["parentType"] = $type;
		$params["parentId"] = $id;
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		$params["openEdition"] = Authorisation::isOpenEdition($id, Project::COLLECTION, @$element["preferences"]);

        if(Yii::app()->request->isAjaxRequest){
			echo $controller->renderPartial("addChartSV", $params, true);

		}
    }
}