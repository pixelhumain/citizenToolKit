<?php 

class TranslateEducEtabToPh {

	public static $mapping = array(

		"fields.libelle" => "name",
		// "fields.categorie" => "type",
		// "fields.siret" => "shortDescription",
		"fields.geolocalisation.0" => "geo.latitude",
		"fields.geolocalisation.1" => "geo.longitude",
		"fields.site_web" => "url",
		// "fields.l4_declaree" => "address.streetAddress",
		// "fields.libapen" => "tags.0",
	);
}

?>