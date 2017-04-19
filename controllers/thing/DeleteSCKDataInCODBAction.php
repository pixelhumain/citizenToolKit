<?php
/*19/04/17
* Danzal
*/

class DeleteSCKDataInCODBAction extends CAction {

	public function run($boardId=null,$end=null,$typeThing=null){

		$res=array();
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {

			$interval = new DateInterval('P1D');
			$intervalLimit = new DateInterval('P3M');
			$endLimit = new DateTime(gmdate('Y-m-d'));
			$endLimit->sub($intervalLimit);
			
			$type=(isset($typeThing))? $typeThing : "smartCitizen";
			$where=array("type"=>$type,"boardId"=>$boardId);
			$sort = array("timestamp"=>1);
			$limit=1;
			$fields=array("timestamp");
			$startA = Thing::getLastestRecordsInDB($boardId,$where,$sort,$limit,$fields);
			var_dump($start);
			/*
			if(isset($start)){
				$startD = new DateTime($start);
				if (isset($end)){
					$endD = new DateTime($end);
				} else {$endD = new DateTime($start); }
				if($endD>$endLimit){ $endD=$endLimit; }

				$endD->add($interval);

				$period = new DatePeriod($startD, $interval, $endD);

				foreach ($period as $date) {
					$datef = $date->format('Y-m-d');

					$dataC = Thing::getLastestRecordsInDB($boardId,$where,$datef);
					//$res[] = Thing::getSCKAvgWithRollupPeriod($dataC,$rollupMin,true);
				}
			} */
		} 
		Rest::json($res);
		Yii::app()->end(); 
		/*echo 'dataC : </br>';
		var_dump($dataC);
		echo '</br> res : </br>';
		var_dump($res);*/
	}
}
?>