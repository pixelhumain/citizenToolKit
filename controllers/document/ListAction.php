<?php
class ListAction extends CAction {

	public function run($id, $type,$getSub=false) 
	{
		$controller=$this->getController();

		if( $type == Organization::COLLECTION )
		{
			$organizations = array();
			$organization = Organization::getById($id);
			$organizations[$id] = $organization['name'];
		}
		$documents = Document::getWhere( array( "type" => $type, 
												"id" => $id ,
												"contentKey" => array( '$exists' => false)
		));
		
		if($getSub && $type == Organization::COLLECTION && Authorisation::canEditMembersData($id)) {
			$subOrganization = Organization::getMembersByOrganizationId($id, Organization::COLLECTION);
			
			foreach ($subOrganization as $key => $value) {
				$organization = Organization::getById($key);
				$organizations[$key] = $organization['name'];
				$documents = array_merge( $documents, Document::getWhere( array( "type" => $type, 
																				"id" => $key ,
																				"contentKey" => array( '$exists' => false)
																				)));
			}
		}

		$categories = Document::getAvailableCategories($id, $type);
		$params = array("documents"=>$documents, 
						"id" => $id, 
						"categories" => $categories,
						"organizations" => $organizations,
						"getSub" => $getSub
						);
		if(Yii::app()->request->isAjaxRequest)
			echo $controller->renderPartial("documents",$params,true);
		else
			$controller->render("documents",$params);
	}
	
}