<?php
class PublicAction extends CAction
{
	public function run($id) {
		$controller=$this->getController();

		//get The job Id
		if (empty($id)) {
			throw new CTKException(Yii::t("job","The job posting id is mandatory to retrieve the job posting !"));
		}

		if (empty($_POST["mode"])) {
			$mode = "view";
		} else {
			$mode = $_POST["mode"];
		}

		if ($mode == "insert") {
			$job = array();
			$controller->title = Yii::t("job","New Job Offer");
			$controller->subTitle = Yii::t("job","Fill the form");
		
		} else {
			$job = Job::getById($id);
			$controller->title = $job["title"];
			$controller->subTitle = (isset($job["description"])) ? $job["description"] : ( (isset($job["type"])) ? "Type ".$job["type"] : "");
		}

		$tags = json_encode(Tags::getActiveTags());
		$organizations = Authorisation::listUserOrganizationAdmin(Yii::app()->session["userId"]);

		$controller->pageTitle = Yii::t("job","Job Posting");

		Rest::json(array("result"=>true, 
			"content" => $controller->renderPartial("jobSV", array("job" => $job, "tags" => $tags, "organizations" => $organizations, "mode" => $mode), true)));	
	}
}