<?php
class SaveAction extends CAction
{
	public function run($id,$type) {
		$controller=$this->getController();

		if (isset(Yii::app()->session["userId"])) {
			try {
				$res = Project::insert($_POST, $id,$type);
			} catch (CTKException $e) {
				$res = array("result"=>false, "msg"=>$e->getMessage());
			}
			Rest::json($res);
		} else {
			$res = array("result"=>false, "msg"=>"You must be connected to create a project");
		}
	}
}