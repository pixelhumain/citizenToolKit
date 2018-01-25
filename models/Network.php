<?php 
class Network {

	const COLLECTION = "network";
	const CONTROLLER = "network";

    public static $dataBinding = array (
	    "name" => array("name" => "name", "rules" => array("required")),
	    "visible"=> array("name" => "visible"),
	    "skin"=> array("name" => "skin"),
	    "title"=> array("name" => "skin.title"),
	    "paramsLogo"=> array("name" => "paramsLogo"),
	    "origin"=> array("name" => "origin"),
	    "visible"=> array("name" => "visible"),
	    "add" => array("name" => "add"),

	    "filter" => array("name" => "filter"),
	    "types" => array("name" => "types"),

	    "result" => array("name" => "result"),
	    "displayImage" => array("name" => "displayImage"),

	    "request" => array("name" => "request"),
	    "searchTag" => array("name" => "searchTag"),
	    "tags" => array("name" => "tags"),
	    "scope" => array("name" => "scope"),
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	);

	/**
	 * Récupère le fichier de configuration du network et retourne en tableau json
	 * Le fichier de conf peut être en local sur le serveur ou accessible depuis une URL
	 * TODO : Vérifie le bon formatage du fichier
	 * @return json_decode array
	 */
	public static function getNetworkJson($networkParams) {
		//error_log("NETWOOOOOOOORK PARAMS : ".$networkParams);
		$configPath = "";
		/*if(@$_GET["network"]) {
            Yii::app()->params['networkParams'] = $_GET["network"];
        }*/
        
        if (empty($networkParams)) {
			$configPath = "default";
		} else {
			$configPath = $networkParams;
		}

		if ( stripos($configPath, "http") === false ) {
			error_log("chargement du fichier de config en local");
			$configPath =  Yii::app()->theme->basePath . '/views/layouts/params/'.$configPath.".json";
		}

		try {
			$json = file_get_contents($configPath, null, null, 0, 10000);
			if ($json === false) 
				throw new CHttpException(404, "Impossible to find the network configuration file.");
		} catch (Exception $e) {
    		throw new CHttpException(404, "Error Reading the network configuration file.");
		}

		return json_decode($json, true);
	}

	public static function prepData ($params) {

		// if (isset($params["skin"]["displayCommunexion"]) && !is_bool($params["skin"]["displayCommunexion"])) {
		// 	if ($params["skin"]["displayCommunexion"] == "true")
		// 		$params["skin"]["displayCommunexion"] = true;
		// 	else 
		// 		$params["skin"]["displayCommunexion"] = false;
		// }

		// if(!empty($params["add"])){
		// 	$newAdd = array();
		// 	foreach ($params["add"] as $key => $value) {
		// 		$newAdd[$value] = true ;
		// 	}
		// 	$params["add"] = $newAdd;
		// }

		// if(!empty($params["filter"])){
		// 	$newFilters["linksTag"] = array();
		// 	foreach ($params["filter"] as $key => $value) {
		// 		$i = 0 ;
		// 		$tags = array();
		// 		while ( !empty($value["keyVal".$i] ) && !empty($value["tagskeyVal".$i] ) ) {
		// 			$tags[$value["keyVal".$i]] = preg_split("/[,]+/", $value["tagskeyVal".$i]);


		// 			$i++;
		// 		}

		// 		$newFilters["linksTag"][$value["name"]] = array( 	"tagParent" => "Type",
		// 															"background-color" => "#f5f5f5",
		// 															"image" => "Travail.png",
		// 															"tags" => $tags );
		// 	}

		// 	$params["filter"] = $newFilters;
		// }

		// if(!empty($params["request"]["searchType"]))
		// 	$params["request"]["searchType"] = explode(",", $params["request"]["searchType"]);

		// if(!empty($params["request"]["sourceKey"]))
		// 	$params["request"]["sourceKey"] = explode(",", $params["request"]["sourceKey"]);

		return $params;
	}


	public static function getById($id, $fields=null) {
		$network = PHDB::findOneById( Network::COLLECTION, $id, $fields);
		return $network;
	}

	public static function getNetworkByUserId($id, $fields=null) {
		$where = array("creator" => $id);

		if(Yii::app()->session["userId"] != $id){
			$isLinked = false;
			if(!empty(Yii::app()->session["userId"])){
				$isLinked = Link::isLinked($id, Person::COLLECTION, Yii::app()->session["userId"]);
			}

			if($isLinked){
				$where = array('$and' => array(
								$where,
								array('$or' => array( 
									array("visible"=>"public"), 
									array("visible"=> "network")))));
			}else{
				$where =  array('$and' => array(
								$where,
								array("visible"=>"public")));
			}
		}

		$networks = PHDB::find( Network::COLLECTION, $where, $fields);
		return $networks;
	}

	public static function getListNetworkByUserId($id) {
		$networks = Network::getNetworkByUserId($id);
		$list = array();
		foreach ($networks as $key => $value) {
			$value["type"] = "network";
			$list[$key] = $value;
		}
		return $list;
	}

	public static function getNetwork($id, $type){
		$res = array();
		$listElt = array(Organization::COLLECTION, Person::COLLECTION, Project::COLLECTION, Event::COLLECTION);
		if(in_array($type, $listElt) ){
			$res = PHDB::findOne( $type , array( "_id" => new MongoId($id) ) ,array("urls") );
			$res = (!empty($res["urls"]) ? $res["urls"] : array() );
		}
		return $res;
	}


	public static function afterSave($network, $creatorId){
		$networkId = (string)$network['_id'];
		$isAdmin = true ;
		//if ($isToLink) {
			//Create link in both entity person and organization 
			Link::connect($networkId, Network::COLLECTION, $creatorId, Person::COLLECTION, $creatorId,"followers",$isAdmin);
			Link::connect($creatorId, Person::COLLECTION, $networkId, Network::COLLECTION, $creatorId,"networks",$isAdmin);
		//}

		return array(	"result"=>true,
		    			"msg"=>"Votre network est communectée.", 
		    			"id"=>$networkId, 
		    			"network"=> $network);
	}





}