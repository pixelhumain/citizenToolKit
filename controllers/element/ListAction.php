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
				if($data==Service::COLLECTION){
					$where=array("parentId"=>$id, "parentType"=>$type);
					$params["list"][$data]=Service::getListBy($where);
				}
				if($data==Order::COLLECTION){
					$where=array("customerId"=>Yii::app()->session["userId"]);
					$params["parentList"]=Order::getListByUser($where);
					//print_r($params["orderList"]);
					//exit;
					$params["list"]["orderItems"]=Order::getOrderItemById((string)array_values($params["parentList"])[0]['_id']);
				}
				if($data==Backup::COLLECTION){
					$where=array("parentId"=>Yii::app()->session["userId"]);
					$params["parentList"]=Backup::getListBy($where);
					//print_r($params["orderList"]);
					//exit;
					//$params["list"]["orderItems"]=Order::getOrderItemById((string)array_values($params["orderList"])[0]['_id']);
				}
				
				/*if($data==OrderItem::COLLECTION){
					$where=array("customerId"=>Yii::app()->session["userId"]);
					$params["list"][$data]=OrderItem::getListByUser($where);
				}*/
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
