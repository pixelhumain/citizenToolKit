<?php
class IndexAction extends CAction
{
    public function run( $type=null, $id= null, $year= null )
    {
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
        } 
         
        else if( $type == Event::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/event/dashboard/id/".$id)."'><i class='fa fa-lightbulb-o'></i>Event</a>");
            $event = Event::getById($id);
            $controller->title = $event["name"]."'s Timeline";
            $controller->subTitle = "Every event has steps to get over.";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } 
        /*  CAN EDIT OR NOT   */
		$edit = Authorisation :: canEditItem(Yii::app()->session["userId"], $type, $id);
		///////////////////////
        array_push( $controller->toolbarMBZ, '<a href="#" class="newRoom" title="proposer une " ><i class="fa fa-plus"></i> Room </a>');
		
        $where = array(
                "_id"=>new MongoId($id),
                "tasks" =>  array('$exists' => 1));
  		$tasks = Gantt::getTasks($where,$type);
        if (isset($year)){
	        $period=$year;
	        $newArray=[];
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
			        $val=array("color"=>$val["color"],"name"=>$val["name"],"startDate"=>$startDate,"endDate"=>$endDate);
					$newArray[$key]=$val;
				}
	        }
	        $tasks=$newArray;
		}
		else
			$period="yearly";
		$params = array("tasks" => $tasks,  "period" => $period, "edit" => $edit);
		if(Yii::app()->request->isAjaxRequest)
	        echo $controller->renderPartial("index", $params,true);
	    else
  			$controller->render( "index" , $params );
    }
}