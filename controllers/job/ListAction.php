<?php
class ListAction extends CAction
{
	public function run($organizationId = null, $fullPage=false) {
		$controller=$this->getController();

		$jobList = Job::getJobsList($organizationId);
	  
		if(Yii::app()->request->isAjaxRequest){
			$controller->renderPartial("jobList", array("jobList" => $jobList, "id" => $organizationId, "fullPage" => $fullPage));
		} else {
			$controller->render("jobList", array("jobList" => $jobList, "id" => $organizationId, "fullPage" => $fullPage));
		}
	}
}