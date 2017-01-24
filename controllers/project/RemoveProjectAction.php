<?php
class RemoveProjectAction extends CAction
{
    public function run($projectId) {
		$controller=$this->getController();
		$project=Project::getById($projectId);
		if (isset(Yii::app()->session["userId"]) && isset($project)){
			$res = array( "result" => false , "msg" => Yii::t("common", "Something went wrong!") );
			try {
				$res = Project::removeProject($projectId, Yii::app()->session["userId"]);
			} 
			catch (CTKException $e) {
				$res = array( "result" => false , "msg" => $e->getMessage() );
			}
			//return true;
		}
		else {
			$res = array( "result" => false , "msg" =>  Yii::t("common", "Access denied") );
		}
		return Rest::json($res);
	}
}