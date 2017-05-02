<?php 

class TranslateOdsToPh {

	public static $mapping_activity = array(

		"fields.l1_declaree" => "name",
		"fields.categorie" => "type",
		"fields.coordonnees.0" => "geo.latitude",
		"fields.coordonnees.1" => "geo.longitude",
		"fields.l4_declaree" => "address.streetAddress",
		"fields.libapen" => "tags.0",
	);
}

?>