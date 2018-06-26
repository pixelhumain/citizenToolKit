<?php 

class TranslatePoleEmploiToPh {

	public static $mapping_offres_infotravail = array(

		"ROME_PROFESSION_CARD_NAME" => "name",
		"LONGITUDE" => "geo.longitude",
		"LATITUDE" => "geo.latitude",
		"CONTRACT_TYPE_NAME" => "type",
		// "ROME_PROFESSION_CARD_CODE" => 
		"ROME_PROFESSION_NAME" => "shortDescription",
		"ACTIVITY_NAME" => "tags.0",
		"CONTRACT_TYPE_CODE" => "tags.1",
		"QUALIFICATION_NAME" => "tags.2",
		"DEGREE_TYPE_NAME_1" => "info1",
		"geoP" 	=> array("valueOf" => array(
									"@type" 			=> "GeoCoordinates", 
									"latitude" 			=> array("valueOf" => "LATITUDE"),
									"longitude" 		=> array("valueOf" => "LONGITUDE")
				 					)),
	);

	public static $mapping_offres = array(

		"title" => "name",
		"gpsLongitude" => "geo.longitude",
		"gpsLatitude" => "geo.latitude",
		"contractTypeName" => "type",
		"description" => "description",
		"romeProfessionName" => "tags.0",
		"contractTypeCode" => "tags.1",
		"QUALIFICATION_NAME" => "tags.2",
		"origins.0.originUrl" => "url",
		"postcode" => "address.postalCode",
		"cityCode" => "address.codeInsee",
		"cityName" => "address.addressLocality",
		"countryCode" => "address.addressCountry",
		
	);

}

?>