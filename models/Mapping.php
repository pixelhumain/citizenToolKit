<?php
class Mapping {

	const COLLECTION = "mappings";

	public static function insert($mapping, $creatorId) {

		$newMapping = $mapping;
		$newMapping["userId"] = $creatorId;
		$newMapping["key"] = $mapping["name"];

		PHDB::insert( Mapping::COLLECTION, $newMapping);

		return array(
			"result"=>true,
		   	"msg"=>"Votre mapping à bien été sauvegardé.", 
			"newMapping"=> $newMapping);
	
	}

	public static function delete($post, $creatorId) {

		$idMapping = new MongoId($post['idMapping']);

		$where = array('_id' => $idMapping);

		PHDB::remove(Mapping::COLLECTION, $where);

		return array(
			"result"=>true,
		   	"msg"=>"Votre mapping à bien été supprimé.");

	}

	public static function update($post, $creatorId) {

		$idMapping = new MongoId($post['idMapping']);

		$where = array('_id' => $idMapping);
		$newField = $post['mapping']['fields'];

		$newField = self::replaceDot($newField);

		var_dump($newField);

		PHDB::update(Mapping::COLLECTION, $where, array('$set' => array('fields' => $newField)));

		return array(
			"result"=>true,
		   	"msg"=>"Votre mapping à bien été modifié.", 
			"newField"=> $newField);
	}


	public static function replaceDot($newField) {

		foreach ($newField as $key => $value) {

			if (strpos($key, '.')) {
				$newKey = $key;
				$newKey = str_replace(".", "_dot_", $newKey);
				$newField[$newKey] = $value;
				unset($newField[$key]);  
			}
		}

		return $newField;
	}

	public static function replaceByRealDot($newField) {

		foreach ($newField as $key => $value) {

			if (strpos($key, '_dot_')) {
				$newKey = $key;
				$newKey = str_replace("_dot_", ".", $newKey);

				$newField[$newKey] = $value;
				unset($newField[$key]);
			}
		}

		return $newField;
	}

}
?>