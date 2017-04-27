<?php
class HeaderAction extends CAction
{
    public function run($type=null, $id=null, $chart=null){

    	$controller=$this->getController();
		$element = Element::getByTypeAndId($type,$id);
		$params["element"] = $element;
		$params["parentType"] = $type;
		$params["parentId"] = $id;
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		$params["openEdition"] = Authorisation::isOpenEdition($id, $type, @$element["preferences"]);
        if(Yii::app()->request->isAjaxRequest){
			echo $controller->renderPartial("header", $params, true);

		}
    }
}