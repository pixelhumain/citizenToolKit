<?php
class DetailAction extends CAction
{
    public function run($id)
    {
        $controller=$this->getController();
        
        $controller->title = "Action Needs";
        $controller->subTitle = "Define need in order to receive help from community";
        $controller->pageTitle = "Communecter - Action Needs";
        $need=Need::getById($id);
        foreach ($need["links"] as $key => $data){
	        if ($key != "helpers"){
		        foreach ($data as $uid => $value){
			       	$parentType=$value["type"];
			       	$parentId=$uid;
		        }
	        }
        }
        $parent=array();
        $params=array();
        if( $parentType == Project::COLLECTION ) {
            $parent = Project::getById($parentId);
        } 
        else if( $parentType == Organization::COLLECTION ) {
            $parent = Organization::getById($parentId);
        } 
        $limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
		$images = Document::getImagesByKey($parentId,$parentType, $limit);

        $description=array();
        $helpers=array();
        if (isset($need["description"]))
        	$description=$need["description"];
        if(isset($need["links"]["helpers"])){
  			foreach ($need["links"]["helpers"] as $id => $e) {
				$citoyen = Person::getPublicData($id);
				if(!empty($citoyen)){
					$citoyen["type"]="citoyen";
					$citoyen["isValidated"]=$e["isValidated"];
					array_push($helpers, $citoyen);
				}
			}
  		}
  		$admin = false;
		if(isset(Yii::app()->session["userId"]) && isset($_GET["id"]))
			$admin = Authorisation::canEditItem(Yii::app()->session["userId"], $parentType, $parentId);

        $params = array( "need" => $need, "description" => $description, "helpers" => $helpers, "isAdmin" => $admin, "parentType" => $parentType, "parentId" => $parentId, "parent" => $parent, "images" => $images);
        $params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $parentType, $parentId);
		$params["openEdition"] = Authorisation::isOpenEdition($parentId, $parentType, @$parent["preferences"]);
        $page = "detail";
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
		else
  		$controller->render($page, $params);
    }
}