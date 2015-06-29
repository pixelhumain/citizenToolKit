<?php
class RemoveTaskAction extends CAction
{
    public function run($projectId,$taskId) {
		$controller=$this->getController();
		$res = Project::removeTask($projectId, $taskId, Yii::app()->session["userId"]);
		return Rest::json($res);
	}
}