<?php
class NotificationsAction extends CAction
{
    public function run($id, $type)
    {
        $controller=$this->getController();
		$params = array();
		$params["elementId"] = $id;
		$params['elementType'] = $type;
		
		//TODO SBAR - it's not beautifull. Refactor soon
		if($type == Person::COLLECTION){
			$params["parent"] = Person::getPublicData($id);
			$params["controller"] = Person::CONTROLLER;
		}
		else if ($type == Organization::COLLECTION){
			$params["parent"] = Organization::getPublicData($id);
			$params["controller"] = Organization::CONTROLLER;
			$connectType="members";
		}
		else if ($type == Project::COLLECTION){
			$params["parent"] = Project::getPublicData($id);
			$params["controller"] = Project::CONTROLLER;
			$connectType="contributors";
		}
		else if ($type == Event::COLLECTION){
			$params["parent"] = Event::getPublicData($id);
			$params["controller"] = Event::CONTROLLER;
			$connectType="attendees";
		} 
		else{
			throw new CTKException("Impossible to manage this type ".$type);
		}

		if(isset(Yii::app()->session["userId"])){
			$params["canEdit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
			if(@$params["parent"]["links"] && @$params["parent"]["links"][$connectType]){
				$confirmation=array("asAdmin"=>array(),"asMember"=> array(), "connectType"=> $connectType);
				foreach($params["parent"]["links"][$connectType] as $key => $data){
					if(@$data["toBeValidated"] || @$data["isAdminPending"]){
						$needAdminAction=true;
						$member=Element::getElementSimpleById($key,Person::COLLECTION);
						if(@$data["isAdminPending"])
							$confirmation["asAdmin"][$key]=$member;
						else
							$confirmation["asMember"][$key]=$member;
					}
				}
				if(@$needAdminAction)
					$params["confirmations"]=$confirmation;
			}
		}

		//$params['controllerId'] = $controllerId;
		//$contentKey=null;
		//$params["authorizedToStock"]= Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE);
		//$params['images'] = Document::getListDocumentsByIdAndType($id, $type, $contentKey, Document::DOC_TYPE_IMAGE);
		$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $params["parent"]["_id"]);
        $params["openEdition"] = Authorisation::isOpenEdition($params["parent"]["_id"], $type, @$params["parent"]["preferences"]);
        
		$controller->subTitle = "";
		echo $controller->renderPartial("notifications", $params);
    }
}