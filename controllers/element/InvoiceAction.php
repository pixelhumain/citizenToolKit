<?php
class InvoiceAction extends CAction
{
    public function run($type, $id) { 
		$controller=$this->getController();
		$params=array("type"=>$type,"id"=>$id, "actionType"=>$_POST["actionType"],"order"=>[]);
		$params["order"] = Order::getOrderItemForInvoiceByIdUser($id);
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