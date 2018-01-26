<?php

class PreviewCoopDataAction extends CAction {

	public function run() { 

		$type 	= @$_POST["type"];
		$dataId 	= @$_POST["dataId"];

		$controller=$this->getController();

		$data = Cooperation::getCoopData(null, null, $type, null, $dataId);

		echo $controller->renderPartial("preview", array("data" => $data, 
														 "type"=> $type, 
													 	 "dataId" => $dataId), true);
		Yii::app()->end();
		

		
	}



}
