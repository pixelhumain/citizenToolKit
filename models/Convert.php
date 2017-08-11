<?php
class Convert {

	public static function getPrimaryParam($map) {

		$param = array();
		$param['typeElement'] = Event::COLLECTION;

        foreach ($map as $key => $value) {

            $param['infoCreateData'][$key]["valueAttributeElt"] = $value;
            $param['infoCreateData'][$key]["idHeadCSV"] = $key;
        }

        $param['typeFile'] = 'json';
        $param['warnings'] = false;

        return $param;
	}

	public static function getCorrectUrlForOdsAndDatanova() {

		$url_final = "";
		$url_ods_head ="";
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

		return $url_complete;
	}

	public static function convertOdsToPh($url) {

		$map = TranslateOdsToPh::$mapping_activity;

		$url_complete = self::getCorrectUrlForOdsAndDatanova();

		$url_complete = str_replace("_", ".", $url_complete);

		$param = self::getPrimaryParam($map);

        $param['key'] = 'convert_ods';
        $param['nameFile'] = 'convert_ods';
        $param['pathObject'] = 'records';

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

        if ($result['result'] !== false) {
			$res = json_decode($result['elements']);
		} else {
			$res = [];
		}

        return $res;
	}

	public static function convertEducMembreToPh($url) {
		$map = TranslateEducMembreToPh::$mapping;

		$url_complete = self::getCorrectUrlForOdsAndDatanova();
		$url_complete = str_replace("geofilter_polygon", "geofilter.polygon", $url_complete);

		$param = self::getPrimaryParam($map);

        $param['key'] = 'convert_educ_membre';
        $param['nameFile'] = 'convert_educ_membre';
        $param['pathObject'] = 'records';

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

        if ($result['result'] !== false) {
			$res = json_decode($result['elements']);
		} else {
			$res = [];
		}

        return $res;
	}

	public static function convertEducEcoleToPh($url) {

		$map = TranslateEducEcoleToPh::$mapping;

		$url_complete = self::getCorrectUrlForOdsAndDatanova();
		$url_complete = str_replace("geofilter_polygon", "geofilter.polygon", $url_complete);

		$param = self::getPrimaryParam($map);

        $param['key'] = 'convert_educ_ecole';
        $param['nameFile'] = 'convert_educ_ecole';
        $param['pathObject'] = 'records';

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

        if ($result['result'] !== false) {
			$res = json_decode($result['elements']);
		} else {
			$res = [];
		}

        return $res;
	}

	public static function convertEducEtabToPh($url) {

		$map = TranslateEducEtabToPh::$mapping;
		$url_complete = self::getCorrectUrlForOdsAndDatanova();
		$url_complete = str_replace("geofilter_polygon", "geofilter.polygon", $url_complete);

		// var_dump($url_complete);

		$param = self::getPrimaryParam($map);

        $param['key'] = 'convert_educ_etab';
        $param['nameFile'] = 'convert_educ_etab';
        $param['pathObject'] = 'records';

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

        if ($result['result'] !== false) {
			$res = json_decode($result['elements']);
		} else {
			$res = [];
		}

        return $res;

	}

	public static function convertEducStructToPh($url) {
		$map = TranslateEducStructToPh::$mapping;

		$url_complete = self::getCorrectUrlForOdsAndDatanova();
		$url_complete = str_replace("geofilter_polygon", "geofilter.polygon", $url_complete);

		$param = self::getPrimaryParam($map);

        $param['key'] = 'convert_educ_struct';
        $param['nameFile'] = 'convert_educ_struct';
        $param['pathObject'] = 'records';

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

        if ($result['result'] !== false) {
			$res = json_decode($result['elements']);
		} else {
			$res = [];
		}

        return $res;


	}

	public static function convertDatanovaToPh($url) {

		$map = TranslateDatanovaToPh::$mapping_activity;

		$url_complete = self::getCorrectUrlForOdsAndDatanova();

		$url_complete = str_replace("geofilter_polygon", "geofilter.polygon", $url_complete);

        $param = self::getPrimaryParam($map);

        $param['key'] = 'convert_datanova';
        $param['nameFile'] = 'convert_datanova';
        $param['pathObject'] = 'records';
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

        if ($result['result'] !== false) {
			$res = json_decode($result['elements']);
		} else {
			$res = [];
		}

        return $res;

	}

	public static function convertOsmToPh($url) {

		$map = TranslateOsmToPh::$mapping_element;

        $param = self::getPrimaryParam($map);

        $param['pathObject'] = 'elements';
        $param['nameFile'] = 'convert_osm';
        $param['key'] = 'convert_osm';

        $pos = strpos($url, "=");

		$url_head = substr($url, 0, ($pos+1));
		$url_param = substr($url, ($pos+1));

		$url_osm = $url_head . urlencode($url_param);

        if (isset($url_osm)) {
        	$param['file'][0] = file_get_contents($url_osm);
        }

        $result = Import::previewData($param);

        if ($result['result'] !== false) {
			$res = json_decode($result['elements']);
		} else {
			$res = [];
		}

        return $res;
	}

	public static function convertDatagouvToPh($url) {

		$all_data = array();

		$map = TranslateDatagouvToPh::$mapping_datasets;
		$param = self::getPrimaryParam($map);

        $param['typeFile'] = 'json';
        $param['key'] = 'convert_datagouv';
        $param['nameFile'] = 'convert_datagouv';
		$list_dataset = json_decode(file_get_contents($url), true);

		foreach ($list_dataset as $key => $value) {

			$url_orga = "https://www.data.gouv.fr/api/1/datasets/".$value['id']."/";
			$data_orga = json_decode((file_get_contents($url_orga)), true);

			array_push($all_data, $data_orga);
		}

		$param['file'][0] = json_encode($all_data);

		$result = Import::previewData($param);

		if ($result['result'] !== false) {
			$res = json_decode($result['elements']);
		} else {
			$res = [];
		}

        return $res;
	}

	public static function convertWikiToPh($url, $text_filter) {

		$all_data = array();

		$map = TranslateWikiToPh::$mapping_element;

        $param = self::getPrimaryParam($map);

        $param['key'] = 'convert_wiki';
        $param['nameFile'] = 'convert_wiki';

        $wikidata_page_city = json_decode(file_get_contents($url), true);

        $pos_wikidataID = strrpos($url, "Q");

        $wikidataID = substr($url, $pos_wikidataID);
        $wikidataID = substr($wikidataID, 0, strpos($wikidataID, "."));

        $label_dbpedia = $wikidata_page_city["entities"][$wikidataID]["sitelinks"]["frwiki"]["title"];

        // $url_wikipedia = "https://fr.wikipedia.org/wiki/".str_replace(" ", "_", $label_dbpedia);
        // var_dump($url_wikipedia);

        $wikidata_article = json_decode(file_get_contents("https://query.wikidata.org/sparql?format=json&query=SELECT%20DISTINCT%20%3Fitem%20%3FitemLabel%20%3FitemDescription%20%3Fcoor%20%3Frange%20WHERE%20{%0A%20%3Fitem%20wdt%3AP131%20wd%3A".$wikidataID.".%0A%20%3Fitem%20%3Frange%20wd%3A".$wikidataID.".%0A%20%3Fitem%20wdt%3AP625%20%3Fcoor.%0A%20SERVICE%20wikibase%3Alabel%20{%20bd%3AserviceParam%20wikibase%3Alanguage%20%22fr%22.%20}%0A}"), true);

        $key_wikidata_item = 0;

        foreach ($wikidata_article['results']['bindings'] as $key => $value) {

        	if ($text_filter !== null) {

        		if(stristr($value['itemLabel']['value'], $text_filter) == true) {

			    	$all_data[$key_wikidata_item] = array();

		        	if (@$value['coor']) {
		        		$coor = self::getLatLongWikidataItem($value);
		        		array_push($all_data[$key_wikidata_item], $value['itemLabel']['value'], $coor, $value['item']['value'], @$value['itemDescription']['value'], @$value['itemDescription']['value']);
		        	}
		        	$key_wikidata_item++;
				}
        	} else {

        		$all_data[$key_wikidata_item] = array();

	        	if (@$value['coor']) {
	        		$coor = self::getLatLongWikidataItem($value);
	        		array_push($all_data[$key_wikidata_item], $value['itemLabel']['value'], $coor, $value['item']['value'], @$value['itemDescription']['value'], @$value['itemDescription']['value']);
	        	}

	        	$key_wikidata_item++;
        	}        	
        }

        $param['file'][0] = json_encode($all_data);

		$result = Import::previewData($param);

        if ($result['result'] !== false) {
			$res = json_decode($result['elements']);
		} else {
			$res = [];
		}

        return $res;

	}

	public static function convertPoleEmploiToPh($url, $activity_letters = null) {

		//CODE POUR FORGER UN ACCESS TOKEN NECESSAIRE POUR INTEROGER L'API

		$curl = curl_init();
		 
		curl_setopt($curl, CURLOPT_URL, "https://entreprise.pole-emploi.fr/connexion/oauth2/access_token?realm=%2Fpartenaire");

		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=PAR_communecter_9cfae83c352184eff02df647f08661355f3be7028c7ea4eda731bf8718efbfff&client_secret=62a4a6aa2d82fa201eca1ebb3df639882d2ed7cd75284486aaed3a436df67e55&scope=application_PAR_communecter_9cfae83c352184eff02df647f08661355f3be7028c7ea4eda731bf8718efbfff api_infotravailv1"); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$token = curl_exec($curl);
		 
		curl_close($curl);

		$token_final = json_decode($token, true);

		$curl2 = curl_init();

		$pos = strpos($url, "=");

		$url_head = substr($url, 0, ($pos+1));
		$url_param = substr($url, ($pos+1));

		$url = $url_head . urlencode($url_param);

		curl_setopt($curl2, CURLOPT_URL, $url);
		curl_setopt($curl2, CURLOPT_HTTPHEADER, array("Authorization: Bearer ".$token_final["access_token"]));

		curl_setopt($curl2, CURLOPT_RETURNTRANSFER, 1);
		$offres = curl_exec($curl2);
		 
		curl_close($curl2);

		$offres_final = json_decode($offres, true);

		$map = TranslatePoleEmploiToPh::$mapping_offres;

        $param = self::getPrimaryParam($map);

        $param['pathObject'] = 'records';
        $param['key'] = 'convert_poleemploi';
        $param['nameFile'] = 'convert_poleemploi';

        $offres_array = [];
        $offres_array['records'] = [];

        if ($activity_letters == null) {
        	foreach ($offres_final["result"]["records"] as $key => $value) {
	        	$offres_array['records'][$key] = $value;
	        }
        } else {

        	$letters = explode(",", $activity_letters);

        	foreach ($offres_final["result"]["records"] as $key => $value) {

	        	$first_letter = $value["ROME_PROFESSION_CARD_CODE"][0];

	        	if (in_array($first_letter, $letters)) {
	        		$offres_array['records'][$key] = $value;
	        	}
	        }
        }
		
		if (isset($url)) {
        	$param['file'][0] = json_encode($offres_array);
        }

        $result = Import::previewData($param);

        if ($result['result'] !== false) {
			$res = json_decode($result['elements']);
		} else {
			$res = [];
		}

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