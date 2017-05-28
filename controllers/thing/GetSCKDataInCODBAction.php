<?php
/*10/04/17
* Danzal
*/

class GetSCKDataInCODBAction extends CAction {
//Accept un appel pour un seul boardId, mais est prévue pour 
	public function run($boardId=null,$rollupMin=10,$start=null,$end=null,$listBoardIds=null){

		$res=array();
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	
			if(empty($listBoardIds) && !empty($boardId))
				$boardIds=array($boardId);
			else
				$boardIds=json_decode($listBoardIds,true);

			$interval = new DateInterval('P1D');
			
			if(isset($start) && is_array($boardIds)){
				$startD = new DateTime($start);
				if (isset($end)){
					$endD= new DateTime($end);
				} else {$endD = new DateTime($start); }
				$endD->add($interval);

				$period = new DatePeriod($startD, $interval, $endD);

				$res = Thing::getSCKDataCODBbyPeriodAndRollup($boardIds, $period, $rollupMin); 
			}
		} 
		Rest::json($res);
		Yii::app()->end(); 
	}
}
?>