<?php
/**
*
*/
class MonitoringAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
    	$allLogs = Log::getSummaryByAction();
    	$controller->renderPartial("monitoring", array("allLogs" => $allLogs));
    }
}