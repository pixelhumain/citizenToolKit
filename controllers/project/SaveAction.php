<?php
class SaveAction extends CAction
{
	public function run($id=null,$type=null) {
		$controller=$this->getController();

		try {
			//if($id == null || $type == Person::COLLECTION){
			//	$id = Yii::app()->session['userId'];
			//	$type = Person::COLLECTION;
			//if( isset($_POST['parentType']) && $_POST['parentType'] == Organization::COLLECTION ){
				$type = $_POST['parentType'];
				$id = $_POST['parentId'];
			//} 
			//else {
			//	$type = Organization::COLLECTION;
			//} 

			$res = Project::insert($_POST, $id,$type);
		} catch (CTKException $e) {
			$res = array("result"=>false, "msg"=>$e->getMessage());
		}
		Rest::json($res);
	}
}