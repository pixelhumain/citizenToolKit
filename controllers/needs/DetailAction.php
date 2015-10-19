<?php
class DetailAction extends CAction
{
    public function run( $idNeed=null, $type=null, $id= null)
    {
        $controller=$this->getController();
        
        $controller->title = "Action Needs";
        $controller->subTitle = "Define need in order to receive help from community";
        $controller->pageTitle = "Communecter - Action Needs";
        
        if( $type == Project::COLLECTION ) {
            $controller->toolbarMBZ = array("<a href='".Yii::app()->createUrl("/".$controller->module->id."/project/dashboard/id/".$id)."'><i class='fa fa-lightbulb-o'></i>Project</a>");
            $project = Project::getById($id);
            $controller->title = $project["name"]."'s Needs";
            $controller->subTitle = "Need's name // Every Project has a lack of ressources";
            $controller->pageTitle = "Communecter - ".$controller->title;
        } 
        
        array_push( $controller->toolbarMBZ, '<a href="#" class="newNeed" title="proposer une " ><i class="fa fa-plus"></i> Need </a>');
        $description=array();
        $helpers=array();
        $need=Need::getById($idNeed);
        if (isset($need["description"]))
        	$description=$need["description"];
        if(isset($need["links"])){
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
			$admin = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);

        $params = array( "need" => $need, "description" => $description, "helpers" => $helpers, "isAdmin" => $admin );
        $page = "detail";
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
		else
  		$controller->render($page, $params);
    }
}