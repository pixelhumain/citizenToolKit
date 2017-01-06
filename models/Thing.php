<?php 
class Thing {

	const collection = "thing";

	public static $dataBinding = array (
	    "type" => array("name" => "type"),//"smartCitizen"
	    "temp"=>array("name"=>"temp"),
	    "hum"=>array("name"=>"hum"),
	    "light"=>array("name"=>"light"),
	    "bat"=>array("name"=>"bat"),
	    "panel"=>array("name"=>"panel"),
	    "co"=>array("name"=>"co"),
	    "no2"=>array("name"=>"no2"),
	    "noise"=>array("name"=>"noise"),
	    "nets"=>array("name"=>"nets"),
	    "timestamp"=>array("name"=>"timestamp"),
	    "boardId" => array("name"=>"macId"),
	    "userId" => array("name"=>"userId"),

	    "modified" => array("name" => "modified"),
	    "updated" => array("name" => "updated"),
	    "creator" => array("name" => "creator"),
	    "created" => array("name" => "created"),
	);
	
}
?>