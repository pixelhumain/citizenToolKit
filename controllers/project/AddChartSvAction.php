<?php
class AddChartSvAction extends CAction
{
    public function run($id=null){

    	$controller=$this->getController();
		$project = Project::getById($id);
		$params["project"] = $project;
		$params["properties"]=array();
		if (isset($project["properties"]["chart"])){
				$params["properties"]=$project["properties"]["chart"];
			}
		$params["itemId"] = $_GET["id"];
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], Project::COLLECTION, $id);
		$params["openEdition"] = Authorisation::isOpenEdition($id, Project::COLLECTION, @$project["preferences"]);

        if(Yii::app()->request->isAjaxRequest){
			echo $controller->renderPartial("addChartSV", $params, true);

		}
    }
}