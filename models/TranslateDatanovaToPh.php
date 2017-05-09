<?php 

class TranslateDatanovaToPh {

	public static $mapping_activity = array(

		"fields.libelle_du_site" => "name",
		"recordid" => "type",
		"fields.identifiant_a" => "shortDescription",
		"fields.latlong.0" => "geo.latitude",
		"fields.latlong.1" => "geo.longitude",
		"fields.adresse" => "address.streetAddress",
		// "fields.libapen" => "tags.0",
	);
}

?>