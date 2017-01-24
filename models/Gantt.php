<?php 
class Gantt {

	const COLLECTION = "gantts";
	
	public static function getById($id,$type) {
	  	$task = PHDB::findOne($type,array("_id"=>new MongoId($id)));
	  	return $task;
	}

	public static function getWhereSort($params,$sort) {
		$tasks=array();
	  	$tasksKey = PHDB::find(self::COLLECTION,$params);
	  	if(isset($tasksKey)){
	    	foreach ($tasksKey as $key => $value) {
	  			$task = Gantt::getById($key);
	  			array_push($tasks, $task);
	  		}
	    }
	  	return $tasks;
	}
	public static function getTasks($where,$type){
		$res = PHDB::findOne($type, $where);
		$tasks=array();
		if(isset($res)){
	    	foreach ($res["tasks"] as $key => $value) {
	  			$tasks[$key]= $value;
	  		}
	    }
	  	return $tasks;
	}
	
	/*public static function insert($params){
		PHDB::insert(self::COLLECTION,$params);
		return array("result"=>true, "msg"=>"Votre tâche est communecté.","idTask"=>$params["_id"]);
	}*/
	public static function removeTask($taskId,$parentType,$parentId){
		
		//$res = PHDB::remove(self::COLLECTION,array("_id" => new MongoId($taskId)));
		$res = PHDB::update( $parentType, 
                       array("_id" => new MongoId($parentId)) , 
                       array('$unset' => array("tasks.".$taskId => 1)));
        return array("result"=>true, "msg"=>$res);
	}
	public static function saveTask($task){
		$type=$task["parentType"];
		$id=$task["parentId"];
		$taskArray=array(
			"name"=> $task["taskName"],
			"color" => $task["taskColor"],	
			"startDate" => $task["taskStart"],
			"endDate" => $task["taskEnd"]
			);
		$idTask=new MongoId();
		//$update=array("task.".$inc.""=>$taskArray);		
	    PHDB::update($type,
			array("_id" => new MongoId($id)),
            array('$set' => array("tasks.".$idTask  => $taskArray))
        );
      //  Notification::createdObjectAsParam(Person::COLLECTION,Yii::app() -> session["userId"], Gantt::COLLECTION, $idTask, $task["parentType"], $task["parentId"], null, null,null);
		return array("result"=>true, "msg"=>"Votre task a été ajoutée avec succès","idTask" => $idTask);
	}

}