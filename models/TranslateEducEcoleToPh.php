<?php 

class TranslateEducEcoleToPh {

	public static $mapping = array(

		"fields.libelle" => "name",
		// "fields.categorie" => "type",
		// "fields.siret" => "shortDescription",
		"fields.geolocalisation.0" => "geo.latitude",
		"fields.geolocalisation.1" => "geo.longitude",
		"fields.site_web" => "url",
		"fields.mail" => "email",
		"fields.groupe_disciplinaire" => "tags.0",

	);
}

?>