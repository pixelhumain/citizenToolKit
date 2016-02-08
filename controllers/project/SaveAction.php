<?php
class SaveAction extends CAction
{
	public function run($id=null,$type=null) {
		$controller=$this->getController();

		try {
			if($id == null || $type == "citoyen"){
				$id = Yii::app()->session['userId'];
				$type = Person::COLLECTION;
			} else if( isset($_POST['type']) && $_POST['type'] == Organization::COLLECTION ){
				$type = Organization::COLLECTION;
				$id = $_POST['organizationId'];
			} 
			else {
				$type = Organization::COLLECTION;
			} 

			$res = Project::insert($_POST, $id,$type);
		} catch (CTKException $e) {
			$res = array("result"=>false, "msg"=>$e->getMessage());
		}
		Rest::json($res);
	}
}