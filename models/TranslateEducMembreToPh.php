<?php 

class TranslateEducMembreToPh {

	public static $mapping = array(

		"fields.nom" => "name",
		// "fields.categorie" => "type",
		// "fields.siret" => "shortDescription",
		"fields.geolocalisation.0" => "geo.latitude",
		"fields.geolocalisation.1" => "geo.longitude",
		"fields.site_web" => "url",
		"fields.sexe" => "tags.0",
		"fields.etablissement" => "tas.1",
		"fields.secteur_disciplinaire" => "tags.2",
		// "fields.l4_declaree" => "address.streetAddress",
		// "fields.libapen" => "tags.0",
	);
}

?>