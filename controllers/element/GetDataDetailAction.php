<?php

class GetDataDetailAction extends CAction {
/**
* Dashboard Organization
*/
    public function run($type, $id, $dataName) { 
    	//$controller=$this->getController();
		$element = Element::getByTypeAndId($type, $id);

		if($dataName == "community"){
			$links=@$element["links"];
			$contextMap = Element::getAllLinks($links,$type, $id);
			return Rest::json($contextMap);
			Yii::app()->end();
		}


		if($dataName == "collection"){

		}


		if($dataName == "needs"){

		}


		if($dataName == "poi"){

		}


		
	}
}

?>