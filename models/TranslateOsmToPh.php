<?php 

class TranslateOsmToPh {

	public static $mapping_element = array(

		"tags.name" => "name",
		"type" => "type",
		"lat" => "geo.latitude",
		"lon" => "geo.longitude",
		"tags.amenity" => "tags.0",
		// "id" => "description",
	);

}

?>