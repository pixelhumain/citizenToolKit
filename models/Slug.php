<?php

class Slug {
	const COLLECTION = "slugs";
	public static function save($type, $id, $slug){
		PHDB::insert(self::COLLECTION,
			array("id"=>$id,"type"=>$type,"name"=>$slug)
		);
		return true;
	}
	public static function update($type, $id, $slug){
		PHDB::update(self::COLLECTION,
			array("id"=>$id,"type"=>$type),
			array('$set'=>array("name"=>$slug))
		);
		return true;
	}
	public static function getByTypeAndId($type,$id){
		return PHDB::findOne(self::COLLECTION,array("type"=>$type,"id"=>$id));
	}
	public static function check($params){
		$res=PHDB::findOne(self::COLLECTION,array("name"=>$params["slug"], "type"=>array('$ne'=>$params["type"]),"id"=>array('$ne'=>$params["id"])));
		if(!empty($res))
			return false;
		else 
			return true;
	}
}

