<?php
/*10/04/17
* Danzal
*/

class GetSCKDataInCODBAction extends CAction {

	public function run($boardId=null,$rollupMin=10,$start=null,$end=null){

		$res=array();
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {

			$interval = new DateInterval('P1D');
			
			if(isset($start)){
				$startD = new DateTime($start);
				if (isset($end)){
					$endD= new DateTime($end);
				} else {$endD = new DateTime($start); }
				$endD->add($interval);

				$period = new DatePeriod($startD, $interval, $endD);
				
				foreach ($period as $date) {
					$resTemp = array();
					$datef = $date->format('Y-m-d');
					$dataC = Thing::getConvertedRercord($boardId,false,$datef);
					$resTemp = Thing::getSCKAvgWithRollupPeriod($dataC,$rollupMin,false);
					if(!empty($resTemp))
						$res = array_merge($res,$resTemp);
				}
			}
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