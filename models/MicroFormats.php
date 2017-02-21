<?php 
class MicroFormats {

	const COLLECTION = "microformats";

	//See findOrganizationByCriterias...
	public static function getWhere($params, $fields=null) {
	  	return PHDB::find(self::COLLECTION,$params,$fields);
	}

	
}
?>