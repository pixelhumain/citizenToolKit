<?php
class CleanTags {

	public static function cleanAllTags($collection, $doc, $key, $key3) {

		PHDB::update( 
			$collection, array("_id" => $doc['_id']) , array('$unset' => array("tags.".$key3."" => ""))
		);
		PHDB::update( 
			$collection, array("_id" => $doc['_id']) , array('$addToSet' => array("tags" => $key))
		);
	}	
}
?>