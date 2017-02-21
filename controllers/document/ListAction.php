<?php
class ListAction extends CAction {

	public function run($id, $type,$getSub=false,$tpl=null) 
	{
		$controller=$this->getController();
		
		$controller->title = "RESSOURCES";
		$controller->subTitle = "Toutes les ressources de nos associations";
		$controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

		if( $type == Organization::COLLECTION )
		{
			$organizations = array();
			$organization = Organization::getById($id);
			$organizations[$id] = $organization['name'];
		}
		$documents = Document::getWhere( array( "type" => $type, 
												"id" => $id ) );
		
		if($getSub && $type == Organization::COLLECTION && Authorisation::canEditMembersData($id)) {
			$subOrganization = Organization::getMembersByOrganizationId($id, Organization::COLLECTION);
			
			foreach ($subOrganization as $key => $value) 
			{
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
						"getSub" => $getSub
						);
		if(@$organizations)
			$params["organizations"] = $organizations;

		if( @$tpl == "json" ){
			 $documentsLight = array(
			 	"list"=>array(),
			 	"element" => Element::getByTypeAndId($type, $id, array("name"))
			 	);
			foreach (  $documents as $k => $v) {
				$documentsLight["list"][$k] = array(
					"path" => Yii::app()->getRequest()->getBaseUrl(true)."/upload/communecter/".$v["folder"],
					"name" => $v["name"],
					"id" => (string)$v["_id"],
					"author" => $v["author"],
				);
			}
			Rest::json( $documentsLight );
		}
		else if( Yii::app()->request->isAjaxRequest )
			echo $controller->renderPartial("documents",$params,true);
		else
			$controller->render("documents",$params);
	}
	
}