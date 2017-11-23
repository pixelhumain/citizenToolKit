<?php

class CreateAction extends CAction
{
    public function run($id) {
		$controller=$this->getController();

		$order = Order::getById($id);
		$person = Person::getById($order["customerId"]);
		$arrayId =array();
		foreach ($order["orderItems"] as $key => $value) {
			$arrayId[] = new MongoId($value);
		}

		$orderItem = OrderItem::getByArrayId($arrayId, $fields = array());
		$newOrderItem = array();
		foreach ($orderItem as $key => $value) {
			if($value["orderedItemType"] == Service::COLLECTION ){
				$elt = Service::getById($value["orderedItemId"]);
			} else if($value["orderedItemType"] == Product::COLLECTION ){
				$elt = Product::getById($value["orderedItemId"]);
			}
			$newOrderItem[] = array(	"description" => $elt["name"],
										"quantity" => $value["quantity"],
										"price" => $elt["price"],
										"totalPrice" => $value["price"]);
		}

		$tpl = $controller->renderPartial('application.views.pdf.factureTerla', 
				array(	"img1" => "http://127.0.0.1".Yii::app()->theme->baseUrl."/assets/img/LOGOS/terla/logo-min.png",
						"order" => $order,
						"person" => $person,
						"orderItem" => $newOrderItem), true);
		Pdf::createPdf($tpl);
    }
}
?>