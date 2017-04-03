<?php

class Export
{ 
	public static function toCSV($data, $separ=";", $separText=" ") {


		$listbranch=array();
		$listvaluebranch=array();
		$res = json_encode($data);
		$head = array();
		$value_elt = array();

		foreach ($data as $key1 => $value1) {

			$allPath = ArrayHelper::getAllPathJson(json_encode($value1));

			foreach ($allPath as $key2 => $value2) {
				$value = ArrayHelper::getValueByDotPath($value1, $value2);
				array_push($head, htmlspecialchars($value2));
				array_push($value_elt, htmlspecialchars($value));

			}
		
		}

		$head = implode($separText.$separ.$separText, $head);
		$value_elt = implode($separText.$separ.$separText, $value_elt);

		$csv = $separText.$head.$separText;
		$csv .= "<BR>";
		$csv .= $separText.$value_elt.$separText;

		// var_dump($csv);
		// $list = array (
		//   array($head),
		//   array($value_elt)	
		// );

		// var_dump($list);

		// $fp = fopen('/home/damien/workspace/travail/test_crea_csv/file.csv', 'w');
		// var_dump($fp);

		// foreach ($list as $ligne) {
		// 	fputcsv($fp, $ligne, $separ, $separText, $separText);
		// }

		// fclose($fp);
	}

}

