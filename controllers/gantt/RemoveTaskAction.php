<?php
class RemoveTaskAction extends CAction
{
    public function run($taskId,$parentType,$parentId) {
		$controller=$this->getController();
		$res = Gantt::removeTask($taskId,$parentType,$parentId);
		return Rest::json($res);
	}
}