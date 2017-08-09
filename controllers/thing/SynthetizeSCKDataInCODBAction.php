<?php
/*19/04/17
* Danzal
* Modified : 30/05/2017
* synthetise fait une moyenne et enregistre un nouveau record avec minmaxstddev avant la supression
* supression des plus vieux enregistrement (mois par mois) jusqu'à la date fourni $end
* si $end n'est pas fourni on garde les 2 ou 3 derniers mois (on peut changer limit pour gardé plus de mois)
*
*/

class SynthetizeSCKDataInCODBAction extends CAction {

	public function run($boardId=null,$end=null,$rollupMin=60,$boardIds=null){
		//TODO mettre une validation de suppression par admin uniquement 
		//TODO mettre une alerte de la taille base de donné
		//TODO utiliser $boardIds (un array) pour supprimer les enregistrements de plusieurs kits

		$res=array();
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {

			$interval = new DateInterval('P1D'); //P1D si en jours | P1M si mois
			$intervalLimit = new DateInterval('P60D'); //P60D si en jours | P2M si mois
			$endLimit = new DateTime(gmdate('Y-m-d'));
			$endLimit->sub($intervalLimit);
			
			$where=array("type"=>"smartCitizen","boardId"=>$boardId);
			$sort = array("timestamp"=>1); //timestamp croissant
			$limit=3; // les trois premiers record 
			$fields=array("timestamp","minmaxstddev"); // peut retourner un "#" vue sur un seul enregistrement 
			$frecordindb=Thing::getLastestRecordsInDB($boardId,$where,$sort,$limit,$fields);
			//$res["firstRecordNotSynthetized"]=$frecordindb;
			//$firstrecordindb = reset($frecordindb); // si tous les records ont un timestamps bien formatté on peux se passé de la boucle foreach suivante et mettre la limit = 1

			//boucle pour eviter le blocage du timestamp vide ou '#' 
			foreach ($frecordindb as $key => $firstrecordindb) {
				if($firstrecordindb["timestamp"]!='#'|| !empty($firstrecordindb["timestamp"])){
					$startD = new DateTime($firstrecordindb["timestamp"]);
					$res["startDateToSynthetize"]=$startD;
					break;
				}
			}
			if(isset($end)){
				$endD = new DateTime($end);
			}
			if(@$endD>$endLimit|| !isset($endD)){ $endD=$endLimit; }
			
		/*
			$endD=$endLimit;
			$startD=new DateTime(gmdate('Y-m-d'));
			$startD->sub($interval);
		*/
			//$endD->add($interval);

			$res["endDateToSynthetize"]=$endD;
			$period = new DatePeriod($startD, $interval, $endD);
			foreach ($period as $date) {
				//var_dump($date);
				$res[]=Thing::synthetizeSCKRecordInDB($boardId,$date,$rollupMin);

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