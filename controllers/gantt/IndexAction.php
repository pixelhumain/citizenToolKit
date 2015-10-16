<?php
class IndexAction extends CAction
{
    public function run( $type=null, $id= null, $year= null, $isAdmin=null )
    {
	    function array_msort($array, $cols)
		{
		    $colarr = array();
		    foreach ($cols as $col => $order) {
		        $colarr[$col] = array();
		        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
		    }
		    $eval = 'array_multisort(';
		    foreach ($cols as $col => $order) {
		        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
		    }
		    $eval = substr($eval,0,-1).');';
		    eval($eval);
		    $ret = array();
		    foreach ($colarr as $col => $arr) {
		        foreach ($arr as $k => $v) {
		            $k = substr($k,1);
		            if (!isset($ret[$k])) $ret[$k] = $array[$k];
		            $ret[$k][$col] = $array[$k][$col];
		        }
		    }
		    return $ret;
		
		}
        $controller=$this->getController();
        
        $controller->title = "Action Tasks";
        $controller->subTitle = "Define tasks in order to show what's next to the community";
        $controller->pageTitle = "Communecter - Action Tasks";
        
        if( $type == Project::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/project/dashboard/id/".$id)."'><i class='fa fa-lightbulb-o'></i>Project</a>");
            $project = Project::getById($id);
            $controller->title = $project["name"]."'s Timeline";
            $controller->subTitle = "Every Project has steps to get over.";
            $controller->pageTitle = "Communecter - ".$controller->title;
            // If event, we add it to the timeline
			if (isset($project["links"]["events"])){
				$taskEvent=[];
				$newArrayEvent=[];
				foreach ($project["links"]["events"] as $idEvent => $e) {
					$eventsProject=Event::getById($idEvent);
					array_push($taskEvent,$eventsProject);	
				}
				if (isset($year)){
			        foreach ($taskEvent as $key => $val){
				        $startDate=date("Y",strtotime($val["startDate"]));
				        $endDate=date("Y",strtotime($val["endDate"]));
				        if ($startDate==$year || (($startDate<$year) && ($endDate>=$year))){	
					        if ($startDate<$year)
								$startDate=$year.'-01-01';
					        else
					        	$startDate=date("Y-m-d",strtotime($val["startDate"]));
					        if ($endDate > $year)
					        	$endDate=$year.'-12-31';
					        else
					        	$endDate=date("Y-m-d",strtotime($val["endDate"]));
					        $valEv=array("color"=>"lorem","name"=>$val["name"],"startDate"=>$startDate,"endDate"=>$endDate,"key"=> array ("address"=> $val["address"]["addressLocality"]));
							$newArrayEvent[]=$valEv;
						}
			        }
			        $taskEvent=$newArrayEvent;
				}
				else{
					foreach ($taskEvent as $key => $val){
						$keyEvent=(string)$val["_id"];
						$startDate=date("Y-m-d",strtotime($val["startDate"]));
				        $endDate=date("Y-m-d",strtotime($val["endDate"]));
						$valEv=array("color"=>"lorem","name"=>$val["name"],"startDate"=>$startDate,"endDate"=>$endDate,"key"=> array ("address"=> $val["address"]["addressLocality"]));
						 $newArrayEvent[]=$valEv;
					}
					$taskEvent=$newArrayEvent;
				}
			}
        } 
        else if( $type == Event::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/event/dashboard/id/".$id)."'><i class='fa fa-lightbulb-o'></i>Event</a>");
            $event = Event::getById($id);
            $controller->title = $event["name"]."'s Timeline";
            $controller->subTitle = "Every event has steps to get over.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } 
        /*  CAN EDIT OR NOT   */
		//$edit = Authorisation :: canEditItem(Yii::app()->session["userId"], $type, $id);
		///////////////////////
        array_push( $controller->toolbarMBZ, '<a href="#" class="newRoom" title="proposer une " ><i class="fa fa-plus"></i> Room </a>');
        
        $where = array(
                "_id"=>new MongoId($id),
                "tasks" =>  array('$exists' => 1));
  		$tasks = Gantt::getTasks($where,$type);
  	
  		$newArray=[];
        if (isset($year)){
	        $period=$year;
	        foreach ($tasks as $key => $val){
		        $startDate=date("Y",strtotime($val["startDate"]));
		        $endDate=date("Y",strtotime($val["endDate"]));
		        if ($startDate==$year || (($startDate<$year) && ($endDate>=$year))){	
			        if ($startDate<$year)
						$startDate=$year.'-01-01';
			        else
			        	$startDate=$val["startDate"];
			        if ($endDate > $year)
			        	$endDate=$year.'-12-31';
			        else
			        	$endDate=$val["endDate"];
			        $val=array("color"=>$val["color"],"name"=>$val["name"],"startDate"=>$startDate,"endDate"=>$endDate,"key"=>$key);
					$newArray[]=$val;
				}
	        }
		}
		else{
			$period="yearly";
			foreach ($tasks as $key => $val){
				$startDate=date("Y-m-d",strtotime($val["startDate"]));
		        $endDate=date("Y-m-d",strtotime($val["endDate"]));
				$val=array("color"=>$val["color"],"name"=>$val["name"],"startDate"=>$startDate,"endDate"=>$endDate,"key"=>$key);
				$newArray[]=$val;
			}
		}
		//Mergin of event line and task
		if (isset($taskEvent))
			$tasksFinal=array_merge_recursive($newArray, $taskEvent);
		else 
			$tasksFinal=$newArray;
		/* Tri des taches par ordre de date croissante*/
		$tasks2 = array_msort($tasksFinal, array('startDate'=>SORT_ASC, 'endDate'=>SORT_ASC));
// Trie les données par volume décroissant, edition croissant
// Ajoute $data en tant que dernier paramètre, pour trier par la clé commune
		$params = array("tasks" => $tasks2,  "period" => $period, "edit" => $isAdmin);
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index", $params,true);
	    else
  			$controller->render( "index" , $params );
    }
    
    
}