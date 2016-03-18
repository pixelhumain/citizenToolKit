<?php
class AddTimesheetSvAction extends CAction
{
    public function run($id = null, $type = null){

    	$controller=$this->getController();
    	//$params = array();
    	//$params["countries"] = OpenData::getCountriesList();
			
            //$tasks = str_replace('\"','"',$_GET["tasks"]);
            //echo $tasks;
            $tasks=array();
            $where = array(
                "_id"=>new MongoId($id),
                "tasks" =>  array('$exists' => 1));
			$res = Gantt::getTasks($where,$type);
			foreach ($res as $key => $val){
				$startDate=date("Y-m-d",strtotime($val["startDate"]));
		        $endDate=date("Y-m-d",strtotime($val["endDate"]));
				$val=array("color"=>$val["color"],"name"=>$val["name"],"startDate"=>$startDate,"endDate"=>$endDate,"key"=>$key);
				$tasks[]=$val;
			}
			$params["tasks"] = $tasks;
            $params["project"]  = Project::getPublicData($id);

        if(Yii::app()->request->isAjaxRequest){
			echo $controller->renderPartial("addTimesheetSV", $params, true);

		}
    }
}