<?php
/**
*
*/
class MonitoringAction extends CAction
{
    public function run( $id=null )
    {
		$controller = $this->getController();
		$summary = Log::getSummaryByAction();
		$actionsToLog = Log::getActionsToLog();
		echo $controller->renderPartial('monitoring', array("summary" => $summary, "actionsToLog" => $actionsToLog));
    }
}