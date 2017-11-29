<?php

class Circuit {
	const COLLECTION = "circuits";
	const CONTROLLER = "circuit";
	
	//TODO Translate
	public static $orderTypes = array(
	   
    );

	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "type" => array("name" => "type"),
	    "subtype" => array("name" => "subtype"),
	    "total"=>array("name" => "total"),
	    "start"=>array("name" => "start"),
	    "end"=>array("name" => "end"),
	   	"currency"=>array("name" => "currency"),
	   	"capacity"=>array("name" => "capacity"),
	   	"frequency"=>array("name" => "frequency"),
	    "name" => array("name" => "name"),
	    "description" => array("name" => "description"),
	    "countQuantity"=>array("name" => "countQuantity"),
	    "shortDescription" => array("name" => "shortDescription"),
	    "media" => array("name" => "media"),
	    "urls" => array("name" => "urls"),
	    "medias" => array("name" => "medias"),
	    "tags" => array("name" => "tags"),
	    "services"=> array("name" => "services"),
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
		$circuits = PHDB::find( self::COLLECTION , $where );
	  	return $circuits;
	}
	public static function insert($circuit, $userId){
		
        try {
        	$valid = DataValidator::validate( self::CONTROLLER, json_decode (json_encode ($circuit), true), null );
        } catch (CTKException $e) {
        	$valid = array("result"=>false, "msg" => $e->getMessage());
        }
        if( $valid["result"]) 
        {
			$circuit["created"] = new MongoDate(time());
			settype($circuit["countQuantity"], "integer");
			settype($circuit["capacity"], "integer");
			settype($circuit["total"], "float");
			PHDB::insert(self::COLLECTION,$circuit);
			return array("result"=>true, "msg"=>Yii::t("common","Your circuit is well registred"), "circuit"=>$circuit);
		}else 
            return array( "result" => false, "error"=>"400",
                          "msg" => Yii::t("common","Something went really bad : ".$valid['msg']) );

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