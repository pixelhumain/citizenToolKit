<?php
class InvoiceAction extends CAction
{
    public function run($type, $id) { 
		$controller=$this->getController();
		$params=array("type"=>$type,"id"=>$id, "orders"=>[]);
		$params["orders"] = Order::getOrderItemForInvoiceByIdUser($id);
		if(Yii::app()->params["CO2DomainName"] == "terla")
			$page="../element/terla/invoice";
		else
			$page = "invoice";
		if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial($page,$params,true);
		else 
			$controller->render( $page , $params );
	}
}