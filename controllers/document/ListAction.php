<?php
class ListAction extends CAction {
	public function run($id, $type) {
		$controller=$this->getController();

		$documents = Document::getWhere( array( "type" => $type, 
												"id" => $id ,
												"contentKey" => array( '$exists' => false)
		));
		
		$categories = Document::getAvailableCategories($id, $type);

		if(Yii::app()->request->isAjaxRequest)
			echo $$controller->renderPartial("documents",array("documents"=>$documents, "id" => $id, "categories" => $categories),true);
		else
			$$controller->render("documents",array("documents"=>$documents, "id" => $id, "categories" => $categories));
	}
	
}