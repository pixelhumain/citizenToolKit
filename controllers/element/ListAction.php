<?php

class ListAction extends CAction {
	public function run($type, $id) { 
		$controller=$this->getController();
		$params=array("type"=>$type,"id"=>$id, "list"=>[]);
		if(@$_POST["list"] && !empty($_POST["list"])){
			foreach($_POST["list"] as $data){
				if($data==Product::COLLECTION){
					$where=array("parentId"=>$id, "parentType"=>$type);
					$params["list"][$data]=Product::getListBy($where);
				}
			}
		}
		
		if(Yii::app()->params["CO2DomainName"] == "terla")
			$page="../element/terla/list";
		else
			$page = "list";
		if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial($page,$params,true);
		else 
			$controller->render( $page , $params );
	}
}
