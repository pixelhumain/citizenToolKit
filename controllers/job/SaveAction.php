<?php
class SaveAction extends CAction
{
	/**
	* Save a job
	* @return an array with result and message json encoded
	*/
	public function run() {
		$controller=$this->getController();

		//insert a new job
		if (empty($_POST["pk"])) {
			foreach ($_POST as $fieldName => $fieldValue) {
				$collectionName = $controller->getCollectionFieldName($fieldName);
				$job[$collectionName] = $fieldValue;
			}
			$res = Job::insertJob($job);
			if ($res["result"]) {
				return Rest::json(array("msg"=>"insertion ok ", "id"=>$res["id"], "job"=>$res["job"]));
			}
		//update an existing job
		} else {
			$jobId = $_POST["pk"];
			
			if (! empty($_POST["name"]) && ! empty($_POST["value"])) {
				$jobFieldName = $_POST["name"];
				$jobFieldValue = $_POST["value"];
				$collectionName = $controller->getCollectionFieldName($jobFieldName);
				Job::updateJobField($jobId, $collectionName, $jobFieldValue, Yii::app()->session["userId"] );
		  	} else {
				return Rest::json(array("result"=>false,"msg"=>Yii::t("common","Uncorrect request")));  
		  	}	
		}
	  	
	  	return Rest::json(array("result"=>true, "msg"=>Yii::t("job","Your job offer has been updated with success"), $jobFieldName=>$jobFieldValue));
	}
}