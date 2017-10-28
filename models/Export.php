<?php

class Export
{ 
	public static function toCSV($data, $separ=";", $separText=" ") {

		$content = array();
		$allElements = "";
		$oneElement = "";
		$csv = "";
		$data = self::getMongoParam($data);

		$data = json_encode($data);
		if(substr($data, 0,1) == "{")
            $allPath = ArrayHelper::getAllPathJson($data); 
        else{
            $allPath = array();
            foreach (json_decode($data,true) as $key => $value) {

            	if ($value != null) {
            		$allPath = ArrayHelper::getAllPathJson(json_encode($value), $allPath); 
            	}
            }
        }

        $data = json_decode($data, true);

		foreach ($data as $key1 => $value1) {
			$value_elt = array();
			foreach ($allPath as $key2 => $value2) {
				$valPath = ArrayHelper::getValueByDotPath($value1, $value2);
				if ($valPath != null) {
					array_push($value_elt, $valPath);
				} 
				else {
					array_push($value_elt, " ");
				}
			}

			array_push($content, $value_elt);
			//Si l'élément n'as pas de champ id
			if ($value_elt[0] != " ") {  		
				$oneElement = implode($separText.$separ.$separText, $value_elt);
				$allElements .= $separText.$oneElement.$separText;
				$allElements .= "\n";
			}
		}

		$head = implode($separText.$separ.$separText, $allPath);
		$csv = $separText.$head.$separText;
		$csv .= "\n";
		$csv .= $allElements;

		echo $csv; 
}


	public static function getMemberOf($id, $type) {
		$data = Element::getByTypeAndId($type, $id);

		$list_orga = array();

		foreach ($data["links"]["memberOf"] as $key => $value) {
			$orga = Element::getByTypeAndId($value['type'], $key );
			$list_orga[] = $orga;
		}
		return $list_orga;
	}

	public static function getMongoParam($data) {

		foreach ($data as $key => $value) {

			if (isset($value["_id"])) {
				$data[$key]['_id'] = (string)$value["_id"];
			}

			if (isset($value['modified'])) {
				$data[$key]['modified'] = (string)$value['modified'];
			}

			if (isset($value['badges'])) {
				$data[$key]['badges'][0]['date'] = (string)$value['badges'][0]['date'];
			}

		}

		return $data;
	}
}

