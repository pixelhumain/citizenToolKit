<?php 

class TranslateEducStructToPh {

	public static $mapping = array(

		"fields.libelle" => "name",
		// "fields.categorie" => "type",
		// "fields.siret" => "shortDescription",
		"fields.geolocalisation.0" => "geo.latitude",
		"fields.geolocalisation.1" => "geo.longitude",
		"fields.etat" => "tags.0",
	);
}

?>