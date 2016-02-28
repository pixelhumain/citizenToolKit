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
	        foreach ($data as $uid => $value){
		       	$parentType=$value["type"];
		       	$parentId=$uid;
	        }
        }
        if( $parentType == Project::COLLECTION ) {
            $project = Project::getById($parentId);
            $controller->title = $project["name"]."'s Needs";
            $controller->subTitle = "Need's name // Every Project has a lack of ressources";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } 
        else if( $parentType == Organization::COLLECTION ) {

            $organization = Organization::getById($parentId);
            $controller->title = $organization["name"]."'s Needs";
            $controller->subTitle = "Need's name // Every Project has a lack of ressources";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } 
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
						$profil = Document::getLastImageByKey($id, Person::COLLECTION, Document::IMG_PROFIL);
						if($profil !="")
						$citoyen["imagePath"]= $profil;
						array_push($helpers, $citoyen);
					}
				}
  		}
  		$admin = false;
		if(isset(Yii::app()->session["userId"]) && isset($_GET["id"]))
			$admin = Authorisation::canEditItem(Yii::app()->session["userId"], $parentType, $parentId);

        $params = array( "need" => $need, "description" => $description, "helpers" => $helpers, "isAdmin" => $admin, "parentType" => $parentType, "parentId" => $parentId );
        $page = "detail";
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
		else
  		$controller->render($page, $params);
    }
}