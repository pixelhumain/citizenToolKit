<?php


class GenerateTimelineAction extends CAction
{
    public function run() {
	    
	    $controller=$this->getController();
		$tasks=$_POST;
		$data=[];
		foreach ($tasks as $val){
			$array = array(array('color'=> $val["color"],'start' => $val["startDate"],'end' => $val["endDate"]));
			$data[$val["name"]]=$array; 
		}
		/*if (isset($_GET["month"])){
			$alpha = array('Janv', 'Fev', 'Mar', 'Avr', 'Mai', 'Jun', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Dec');
			$data=[];
			print_r($tasks);
			foreach ($tasks as $val){
				//if ($val["taskStart"])
				$array = array(array('color'=> $val["color"],'start' => $val["startDate"],'end' => $val["endDate"]));
				$data[$val["name"]]=$array ;
			}
			var_dump($data);
			$args = array(
	        	'id' => 'month',           
				'theme' =>'white',         
				'alpha_first' => 1,        
				'omega_base' => 31,          
				/*'line' => date('m-d'), */      /* Today in format 'm-d' */
				/*'line_text' => "Aujourd'hui",  */  /* Text to display for the line */
				/*'format'=> array(
								"segment_des" => 'du %s au %s',
								"timesheet_format" => 'Y-m-d',
								"date_format" => 'd M',
					)
	        );
			$timelineMonth = new timesheet($alpha, $args, $data );
			$timelineMonth -> display();
		}
		else
			$timelineMonth="non";*/
		//$res = Gantt::removeTask($taskId,$parentType,$parentId);
		$res = array('tasks'=>$data);
		return Rest::json($res);
	}
}