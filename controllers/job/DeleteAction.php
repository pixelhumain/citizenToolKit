<?php
class DeleteAction extends CAction
{
	/**
	 * Delete an entry from the job table using the id
	 */
	public function run($id) {	
		//get The job Id
		if (empty($id)) {
		  throw new CTKException(Yii::t("job","The job posting id is mandatory to retrieve the job posting !"));
		}
		$res = Job::removeJob($id, Yii::app()->session["userId"]);
		Rest::json($res);
	}
}