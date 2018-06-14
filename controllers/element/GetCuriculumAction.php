<?php

class GetCuriculumAction extends CAction {

    public function run($type, $id) { 
    	$cv = Element::getCuriculum($id, $type);
		//return Rest::json($urls);
		$params = array("curiculum"=>$cv);
		
		$controller = $this->getController();
        echo $controller->renderPartial("curiculum", $params, true);
		Yii::app()->end();
	}
}

?>