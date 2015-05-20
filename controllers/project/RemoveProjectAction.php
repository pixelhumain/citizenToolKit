<?php
class RemoveProjectAction extends CAction
{
    public function run($projectId) {
		$controller=$this->getController();
		$project=Project::getById($projectId);
		if (isset(Yii::app()->session["userId"]) && isset($project)){
			$res = array( "result" => false , "msg" => "Something went wrong" );
			try {
				$res = Project::removeProject($projectId);
			} 
			catch (CommunecterException $e) {
				$res = array( "result" => false , "msg" => $e->getMessage() );
			}
			//return true;
		}
		else {
			$res = array( "result" => false , "msg" => "Access denied" );
		}
		return Rest::json($res);
	}
}