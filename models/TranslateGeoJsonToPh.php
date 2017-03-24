<?php 

class TranslateGeoJsonToPh {
	
	public static $mapping_organisation = array(

		"type_elt" => Organization::COLLECTION,

		"properties.name" => "name",
		"geometry.coordinates.0" => "geo.longitude",
		"geometry.coordinates.1" => "geo.latitude"

	);

	public static $mapping_person = array(

		"type_elt" => Person::COLLECTION,

		"properties.name" => "name",
		"geometry.coordinates.0" => "geo.longitude",
		"geometry.coordinates.1" => "geo.latitude"

	);

	public static $mapping_event = array(

		"type_elt" => Event::COLLECTION,

		"properties.name" => "name",
		"geometry.coordinates.0" => "geo.longitude",
		"geometry.coordinates.1" => "geo.latitude"

	);

	public static $mapping_project = array(

		"type_elt" => Project::COLLECTION,

		"properties.name" => "name",
		"geometry.coordinates.0" => "geo.longitude",
		"geometry.coordinates.1" => "geo.latitude"

	);


}