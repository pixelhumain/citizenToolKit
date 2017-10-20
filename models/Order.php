<?php

class Order {
	const COLLECTION = "orders";
	const CONTROLLER = "order";
	
	//TODO Translate
	public static $orderTypes = array(
	   
    );

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "section" => array("name" => "section"),
	    "type" => array("name" => "type"),
	    "subtype" => array("name" => "subtype"),
	    "name" => array("name" => "name", "rules" => array("required")),
	    "address" => array("name" => "address", "rules" => array("addressValid")),
	    "addresses" => array("name" => "addresses"),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "city" => array("name" => "address.codeInsee"),
	    "addressLocality" => array("name" => "address.addressLocality"),
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "geo" => array("name" => "geo"),
	    "geoPosition" => array("name" => "geoPosition"),
	    "description" => array("name" => "description"),
	    "addresses" => array("name" => "addresses"),
	    "parentId" => array("name" => "parentId"),
	    "parentType" => array("name" => "parentType"),
	    "media" => array("name" => "media"),
	    "urls" => array("name" => "urls"),
	    "medias" => array("name" => "medias"),
	    "tags" => array("name" => "tags"),
	    "price" => array("name" => "price"),
	    "devise" => array("name" => "devise"),
	    "contactInfo" => array("name" => "contactInfo", "rules" => array("required")),
	    "toBeValidated"=>array("name" => "toBeValidated"),
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	    );

	

	/**
	 * get a Poi By Id
	 * @param String $id : is the mongoId of the poi
	 * @return poi
	 */
	public static function getById($id) { 
	  	$order = PHDB::findOneById( self::COLLECTION ,$id );
	  	return $order;
	}

	public static function getListBy($where){
		$products = PHDB::find( self::COLLECTION , $where );
	  	return $products;
	}
	public static function insert($order, $userId){
		$order["customerId"]=$userId;
		$order["orderDate"]=new MongoDate(time());
		$order["created"] = new MongoDate(time());
		$orderItems=array();
		foreach ($order["orderItems"] as $key => $value) {
			$res=OrderItem::insert($key,$value,$userId);
			array_push($orderItems, $res["id"]);
		}
		$order["orderItems"]=$orderItems;
		PHDB::insert(self::COLLECTION,$order);
		return array("result"=>true, "msg"=>Yii::t("common","Your payment and reservations are well registred"), "order"=>$order);
	}
	public static function getListByUser($where){
		$allOrders = PHDB::findAndSort( self::COLLECTION , $where, array("created"=>-1));
		/*foreach ($allOrders as $key => $value) {
			$orderedItem=PHDB::findOneById($value["orderedItemType"], $value["orderedItemId"]);
			if(@$value["comment"])
				$allOrders[$key]["comment"]=Comment::getById($value["comment"]);
			//$allBookings[$key] = array_merge($allBookings[$key], Document::retrieveAllImagesUrl($value["id"], $value["type"]));
			$allOrders[$key]["name"] = $orderedItem["name"];
			$allOrders[$key]["description"] = $orderedItem["description"];
			$allOrders[$key]["profilImageUrl"] = @$orderedItem["profilImageUrl"];
			$allOrders[$key]["profilThumbImageUrl"] = @$orderedItem["profilThumbImageUrl"];
			$allOrders[$key]["profilMediumImageUrl"] = @$orderedItem["profilMediumImageUrl"];
		}*/
	  	return $allOrders;
	}
	public static function getOrderItemById($id){
		$order=self::getById($id);
		$orderItems=[];
		foreach($order["orderItems"] as $data){
			$orderItem=OrderItem::getById($data);
			$orderItems[(string)$orderItem["_id"]]=$orderItem;
		}
		return $orderItems;
	}
	/*
	* Increment a comment rating for an order for a specific product or sevrice
	*/
	/*public static function actionRating($params,$commentId){
		$allRating=Comment::buildCommentsTree($params["contextId"], $params["contextType"], Yii::app()->session["userId"], array("rating"));
		$sum=0;
		foreach ($allRating["comments"] as $key => $value) {
			$sum=$sum+$value["rating"];
		}
		if($allRating["nbComment"] != 0)
			$sum=$sum / $allRating["nbComment"] ;
		$average=round( $sum , 1);
		PHDB::update($params["contextType"],array("_id" => new MongoId($params["contextId"])),array('$set'=>array("averageRating"=>$average)));
		PHDB::update(self::COLLECTION,array("_id" => new MongoId($params["orderId"])),array('$set'=>array("comment"=>$commentId)));
	}*/
}
?>