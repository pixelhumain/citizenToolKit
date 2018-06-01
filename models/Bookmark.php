<?php

/* @author Bouboule (CDA) && ??
Activity Streams are made to keep track of activity inside any environment 
- ActivityStream aims to register all modification on open-editing entity 
- It builds also array of notification which is register in activityStream Collection with a param type array @notify 
- It builds news too

 */
 
class Bookmark {

	const COLLECTION = "bookmarks";
	//From Post/Form name to database field name
	public static $dataBinding = array (
	    "name" => array("name" => "name", "rules" => array("required")),
	    //"type" => array("name" => "type"),
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
}
