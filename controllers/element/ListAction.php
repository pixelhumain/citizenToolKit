<?php

class ListAction extends CAction {
	public function run($type, $id) { 
		$controller=$this->getController();
		$params=array("type"=>$type,"id"=>$id, "actionType"=>$_POST["actionType"],"list"=>[]);
		if(@$_POST["category"] && !empty($_POST["category"])){
			foreach($_POST["category"] as $data){
				if($data==Product::COLLECTION){
					$where=array("parentId"=>$id, "parentType"=>$type);
					$params["list"][$data]=Product::getListBy($where);
				}
				if($data==Booking::COLLECTION){
					$where=array("buyingBy"=>Yii::app()->session["userId"]);
					$params["list"][$data]=Booking::getBookingByUser($where);
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
