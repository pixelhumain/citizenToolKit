<?php
class Convert {

	public static function convertOdsToPh($url) {

        $param = array();
		$param['typeElement'] = Event::COLLECTION;

		$map = TranslateOdsToPh::$mapping_activity;

		$url_final = "";
		$url_ods_params = "";

		foreach ($_GET as $key => $value) {
			if ($key == "url") {
				$url_ods_head = $value;	
			} else {
				if (is_array($value)) {
					foreach ($value as $key2 => $value2) {
						$url_ods_params .= "&" . $key . "=" . $value2;
					}
				} else {
					$url_ods_params .= "&" . $key . "=" . $value;
				}
			}
			$url_final = $url_ods_head.$url_ods_params;
		}

		$pos = strpos($url_ods_head, "?");

		$url_ods_head_final = substr($url_ods_head, 0, $pos) . "?";
		$url_param_dataset = substr($url_ods_head, ($pos+1));

		$url_ods_params = $url_param_dataset . $url_ods_params;

		$url_ods_params = str_replace("@", "%40", $url_ods_params);

		$url_complete = $url_ods_head_final . $url_ods_params;

		$url_complete = str_replace("_", ".", $url_complete);

		// var_dump($url_complete);

        foreach ($map as $key => $value) {

            $param['infoCreateData'][$key]["valueAttributeElt"] = $value;
            $param['infoCreateData'][$key]["idHeadCSV"] = $key;
        }

        $param['typeFile'] = 'json';
        $param['key'] = 'convert_ods';
        $param['nameFile'] = 'convert_ods';
        $param['pathObject'] = 'records';
        $param['warnings'] = false;

        $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, $url_complete);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_HEADER, 0);

	    $return = curl_exec ($ch);
	    curl_close ($ch);

        if (isset($url)) {
        	$param['file'][0] = $return;
        }

        $result = Import::previewData($param);

        $res = json_decode($result['elements']);

        return $res;
	}

	public static function convertDatanovaToPh($url) {

		$param = array();
		$param['typeElement'] = Event::COLLECTION;

		$map = TranslateDatanovaToPh::$mapping_activity;

		$url_final = "";
		$url_ods_params = "";

		foreach ($_GET as $key => $value) {
			if ($key == "url") {
				$url_ods_head = $value;	
			} else {
				if (is_array($value)) {
					foreach ($value as $key2 => $value2) {
						$url_ods_params .= "&" . $key . "=" . $value2;
					}
				} else {
					$url_ods_params .= "&" . $key . "=" . $value;
				}
			}
			$url_final = $url_ods_head.$url_ods_params;
		}

		// var_dump($url_final);

		$pos = strpos($url_ods_head, "?");

		$url_ods_head_final = substr($url_ods_head, 0, $pos) . "?";
		$url_param_dataset = substr($url_ods_head, ($pos+1));

		$url_ods_params = $url_param_dataset . $url_ods_params;

		$url_ods_params = str_replace("@", "%40", $url_ods_params);

		$url_complete = $url_ods_head_final . $url_ods_params;

		$url_complete = str_replace("geofilter_polygon", "geofilter.polygon", $url_complete);

		// var_dump($url_complete);

        foreach ($map as $key => $value) {

            $param['infoCreateData'][$key]["valueAttributeElt"] = $value;
            $param['infoCreateData'][$key]["idHeadCSV"] = $key;
        }

        $param['typeFile'] = 'json';
        $param['key'] = 'convert_datanova';
        $param['nameFile'] = 'convert_datanova';
        $param['pathObject'] = 'records';
        $param['warnings'] = false;

        $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, $url_complete);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_HEADER, 0);

	    $return = curl_exec ($ch);
	    curl_close ($ch);

        if (isset($url)) {
        	$param['file'][0] = $return;
        }

        // var_dump($param['file'][0]);

        $result = Import::previewData($param);

        $res = json_decode($result['elements']);

        return $res;

	}

	public static function convertOsmToPh($url) {

		$param = array();
		$param['typeElement'] = Event::COLLECTION;

		$map = TranslateOsmToPh::$mapping_element;

        foreach ($map as $key => $value) {

            $param['infoCreateData'][$key]["valueAttributeElt"] = $value;
            $param['infoCreateData'][$key]["idHeadCSV"] = $key;
        }

        $param['typeFile'] = 'json';
        $param['pathObject'] = 'elements';
        $param['nameFile'] = 'convert_osm';
        $param['key'] = 'convert_osm';
        $param['warnings'] = false;

        $pos = strpos($url, "=");

		$url_head = substr($url, 0, ($pos+1));
		$url_param = substr($url, ($pos+1));

		$url_osm = $url_head . urlencode($url_param);

		// var_dump($url_osm);

        if (isset($url_osm)) {
        	$param['file'][0] = file_get_contents($url_osm);
        }

        // var_dump($param['file'][0]);

        $result = Import::previewData($param);

        $res = json_decode($result['elements']);

        // var_dump($res);

        return $res;
	}

	public static function convertDatagouvToPh($url) {

		$all_data = array();

		$param = array();
		$param['typeElement'] = Event::COLLECTION;

		$map = TranslateDatagouvToPh::$mapping_datasets;

        foreach ($map as $key => $value) {
            $param['infoCreateData'][$key]["valueAttributeElt"] = $value;
            $param['infoCreateData'][$key]["idHeadCSV"] = $key;
        }

        $param['typeFile'] = 'json';
        $param['key'] = 'convert_datagouv';
        $param['nameFile'] = 'convert_datagouv';
        // $param['pathObject'] = 'elements';
        $param['warnings'] = false;

		$list_dataset = json_decode(file_get_contents($url), true);

		foreach ($list_dataset as $key => $value) {

			$url_orga = "https://www.data.gouv.fr/api/1/datasets/".$value['id']."/";

			$data_orga = json_decode((file_get_contents($url_orga)), true);

			array_push($all_data, $data_orga);
		}

		// var_dump($all_data[0]);

		// foreach ($all_data[0] as $key => $value) {
		// 	if ($key == "slug") {
		// 		var_dump($value);				
		// 	}
		// }

		$param['file'][0] = json_encode($all_data);

		// var_dump($all_data);

		$result = Import::previewData($param);

        $res = json_decode($result['elements']);

        // $res = json_decode($result['elementsWarnings']);

        // $res = $all_data[0];

        return $res;
	}

	public static function convertWikiToPh($url, $wikidataID) {

		$all_data = array();
		$param = array();
		$param['typeElement'] = Event::COLLECTION;

		$map = TranslateWikiToPh::$mapping_element;

        foreach ($map as $key => $value) {
            $param['infoCreateData'][$key]["valueAttributeElt"] = $value;
            $param['infoCreateData'][$key]["idHeadCSV"] = $key;
        }

        $param['typeFile'] = 'json';
        $param['key'] = 'convert_wiki';
        $param['nameFile'] = 'convert_wiki';
        // $param['pathObject'] = 'elements';
        $param['warnings'] = false;

        $wikidata_page_city = json_decode(file_get_contents($url), true);

        // $url_int = substr($url, 0, -5);
        $pos_wikidataID = strrpos($url, "Q");

        $wikidataID = substr($url, $pos_wikidataID);
        $wikidataID = substr($wikidataID, 0, strpos($wikidataID, "."));

        // $label_dbpedia = $wikidata_page_city.entities[q].sitelinks.frwiki.title;
        $label_dbpedia = $wikidata_page_city["entities"][$wikidataID]["sitelinks"]["frwiki"]["title"];

        $wikidata_article = json_decode(file_get_contents("https://query.wikidata.org/sparql?format=json&query=SELECT%20DISTINCT%20%3Fitem%20%3FitemLabel%20%3FitemDescription%20%3Fcoor%20%3Frange%20WHERE%20{%0A%20%3Fitem%20wdt%3AP131%20wd%3A".$wikidataID.".%0A%20%3Fitem%20%3Frange%20wd%3A".$wikidataID.".%0A%20%3Fitem%20wdt%3AP625%20%3Fcoor.%0A%20SERVICE%20wikibase%3Alabel%20{%20bd%3AserviceParam%20wikibase%3Alanguage%20%22fr%22.%20}%0A}"), true);

        $key_wikidata_item = 0;

        foreach ($wikidata_article['results']['bindings'] as $key => $value) {

        	$all_data[$key_wikidata_item] = array();

        	if (@$value['coor']) {
        		// var_dump($value['coor']['value']);
        		$coor = self::getLatLongWikidataItem($value);
        		array_push($all_data[$key_wikidata_item], $value['itemLabel']['value'], $coor, $value['item']['value'], @$value['itemDescription']['value'], @$value['itemDescription']['value']);
	        	// array_push($all_data, $coor);
        	}

        	$key_wikidata_item++;
        }

        // var_dump($all_data);

        $param['file'][0] = json_encode($all_data);

        // var_dump($all_data);

		$result = Import::previewData($param);

        $res = json_decode($result['elements']);

        return $res;

	}

	public static function getLatLongWikidataItem($wikidata_item) {

		if (@$wikidata_item['coor']) {
			$coor = explode(" ", $wikidata_item['coor']['value']);
				
			$coor["longitude"] = substr($coor[0], 6);
			$coor["latitude"] =  rtrim($coor[1], ')');

			unset($coor[0]);
			unset($coor[1]);
		}

		return $coor;
	}
}
?>