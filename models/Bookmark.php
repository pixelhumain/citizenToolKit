<?php

/* @author Bouboule (CDA) && ??
Activity Streams are made to keep track of activity inside any environment 
- ActivityStream aims to register all modification on open-editing entity 
- It builds also array of notification which is register in activityStream Collection with a param type array @notify 
- It builds news too

 */
 
class Bookmark {

	const COLLECTION = "bookmarks";
	const CONTROLLER = "bookmark";
	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "name" => array("name" => "name", "rules" => array("required")),
	    "type" => array("name" => "type"),
	    "url" => array("name" => "url"),
	    "parentId" => array("name" => "id"),
	    "parentType" => array("name" => "type"),
	    "address" => array("name" => "address", "rules" => array("addressValid")),
	    "addresses" => array("name" => "addresses"),
	    "streetAddress" => array("name" => "address.streetAddress"),
	    "postalCode" => array("name" => "address.postalCode"),
	    "city" => array("name" => "address.codeInsee"),
	    "addressLocality" => array("name" => "address.addressLocality"),
	    "addressCountry" => array("name" => "address.addressCountry"),
	    "geo" => array("name" => "geo", "rules" => array("geoValid")),
	    "geoPosition" => array("name" => "geoPosition", "rules" => array("geoPositionValid")),
	    "categories" => array("name" => "categories"),
		"keywords" => array("name" => "keywords"),
		"title" => array("name" => "title"),
		"favicon" => array("name" => "favicon"),
		"hostname" => array("name" => "hostname"),
		"nbClick" => array("name" => "hostname"),
	    "description" => array("name" => "description"),
	    "source" => array("name" => "source"),
	    "creator" => array("name" => "creator"),
	    "tags" => array("name" => "tags"),
	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "created" => array("name" => "created"),
	    "locality" => array("name" => "address"),
	    "alert" => array("name" => "alert"),
	    "searchUrl"=>array("name" => "searchUrl"),
	    "mailParams"=>array("name" => "mailParams"),
	    "descriptionHTML" => array("name" => "descriptionHTML")
	);
	public static function getListByWhere($where) {
		return PHDB::findAndSort(self::COLLECTION,$where,array( 'updated' => -1 ));

	}
	public static function getListOfTags($where) {
		$arrayTags=array();
		$res = PHDB::find(self::COLLECTION,$where,array( 'tags' => 1 ));
		foreach($res as $data){
			if(@$data["tags"]){
				foreach($data["tags"] as $value){
					if(@$arrayTags[$value])
						$arrayTags[$value]["count"]++;
					else{
						//$arrayTags[$data]=[];
						$arrayTags[$value]=array("count"=>1);
					}
				}
			}
		}
		return self::sortTags($arrayTags, array('count'=>SORT_DESC));
	}
	public static function save($params){
		$valid = DataValidator::validate( ucfirst(self::CONTROLLER), json_decode (json_encode ($params), true), null);
		if( $valid["result"]){ 
			$params["created"]=time();
			$params["updated"]=time();
			$params["creator"]=Yii::app()->session["userId"];
			PHDB::insert(self::COLLECTION, $params);
	    	$res = array('result'=>true, "msg" => Yii::t("common","The bookmark is succesfully registered"), "value" => $params);
		}else
			$res = array( "result" => false, "error"=>"400",
                          "msg" => Yii::t("common","Something went really bad : ".$valid['msg']) );

        return $res;

	}
	public static function removeById($id){
		PHDB::remove(self::COLLECTION, array("_id"=>new MongoId($id)));
	    $res = array('result'=>true, "msg" => Yii::t("document","Bookmark deleted"), "id" => $id);
	}
	public static function sortTags($array, $cols){
		$colarr = array();
	    foreach ($cols as $col => $order) {
	        $colarr[$col] = array();
	        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower(@$row[$col]); }
	    }
	    $eval = 'array_multisort(';
	    foreach ($cols as $col => $order) {
	        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
	    }
	    $eval = substr($eval,0,-1).');';
	    eval($eval);
	    $ret = array();
	    foreach ($colarr as $col => $arr) {
	        foreach ($arr as $k => $v) {
	            $k = substr($k,1);
	            if(is_string($k)){
	            	if ( empty($ret[$k]) )
		            	$ret[$k] = $array[$k];
		            $ret[$k][$col] = @$array[$k][$col];
	            }
	        }
	    }

	    return $ret;
	}

	public static function sendMailNotif(){
		$res = array();
		$where = array( "type" => "research", "alert" => "true");
		$book = PHDB::find(self::COLLECTION,$where,array());
		//Rest::json($book); exit;
		foreach ($book as $keyB => $valueB) {
			$urlSearch=(@$valueB["searchUrl"]) ? $valueB["searchUrl"] : $valueB["url"];
			if($valueB["parentType"] == Person::COLLECTION && strrpos($urlSearch, "annonces?") !== false){
				$url = explode("annonces?" ,parse_url( $urlSearch, PHP_URL_FRAGMENT));
				parse_str($url[1]);
				//var_dump($searchSType);
				$params = array(
					"countType" => array("classifieds"),
					"indexMin" => 0,
					"initType" => "classifieds",
					"searchType" => array("classifieds"),
					"lastTimes" => $valueB["updated"]
				);

				if(!empty($sourceKey))
					$params["sourceKey"] = $sourceKey ;
				
				if(!empty($text))
					$params["text"] = $text ;

				if(!empty($tags))
					$params["tags"] = explode(",", $tags) ;

				if(!empty($searchSType))
					$params["searchSType"] = $searchSType ;

				if(!empty($section))
					$params["section"] = $section ;

				if(!empty($category))
					$params["category"] = $category ;

				if(!empty($subType))
					$params["subType"] = $subType ;

				if(!empty($priceMin))
					$params["priceMin"] = $priceMin ;

				if(!empty($priceMax))
					$params["priceMax"] = $priceMax ;

				if(!empty($devise))
					$params["devise"] = $devise ;

				if(!empty($zones)){
					$zonesArray = explode(",", $zones);
					foreach ($zonesArray as $keyZ => $valZ) {
						$z = Zone::getById($valZ,array("name", "level"));
						$level = Zone::getLevel($z);
						$loc = array(
							"type" => "level".$level,
							"id" => @$valZ
						);
						$params["locality"][] = $loc ;
					}
				}

				if(!empty($cities)){
					$citiesArray = explode(",", $cities);
					foreach ($citiesArray as $keyC => $valC) {
						if(MongoId::isValid($valC) === true) {
							$loc = array(
								"type" => City::COLLECTION,
								"id" => @$valC
							);
							$params["locality"][] = $loc ;
						} else {
							$cArray = explode("cp", $valC);
							$loc = array(
								"type" => City::COLLECTION,
								"id" => $cArray[0],
								"postalCode" => $cArray[1],
							);
							$params["locality"][] = $loc ;
						}
					}
				}
				
				//Rest::json($params); exit;

				$search = Search::globalAutoComplete($params);

				//Rest::json($search); exit;

				if(!empty($search["results"])){
					$val = array(	"name" => $valueB["name"], 
									"url" => $valueB["url"], 
									"results" => $search["results"]);
					if(!@$res[$valueB["parentId"]]) $res[$valueB["parentId"]]=array();
					$res[$valueB["parentId"]]["search"][] = $val;
					if(@$valueB["mailParams"])
						$res[$valueB["parentId"]]["mailParams"]=$valueB["mailParams"];
					
					// if(empty($res[$valueB["parentId"]])){
					// 	$res[$valueB["parentId"]][] = $val;
					// }else{
					// 	$res[$valueB["parentId"]][] = array_merge($res[$valueB["parentId"]], $search["results"]);
					// }

					$update = PHDB::update( self::COLLECTION, 
											array("_id" => new MongoId($keyB)), 
											array('$set' => array('updated' => time() ) ));

				}
			}
		}

		if(!empty($res)){
			foreach ($res as $keyR => $valueR) {
				Mail::bookmarkNotif($valueR["search"], $keyR, @$valueR["mailParams"]);
			}
		}

	    return $res;
	}

}
