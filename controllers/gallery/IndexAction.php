<?php
class IndexAction extends CAction
{
    public function run($id, $type)
    {
        $controller=$this->getController();
		$params = array();
		$params["itemId"] = $id;
		$params['itemType'] = $type;
		
		//TODO SBAR - it's not beautifull. Refactor soon
		if($type == Person::COLLECTION){
			$controllerId = "person";
			$params["person"] = Person::getPublicData($id);
		}
		else if ($type == Organization::COLLECTION){
			$controllerId = "organization";
			$params["organization"] = Organization::getPublicData($id);
		}
		else if ($type == Project::COLLECTION){
			$controllerId = "project";
			$params["project"] = Project::getPublicData($id);
		}
		else if ($type == Event::COLLECTION){
			$controllerId = "event";
			$params["event"] = Event::getPublicData($id);
		}
		else{
			throw new CTKException("Impossible to manage this type ".$type);
		}

		if(isset(Yii::app()->session["userId"]))
			$params["canEdit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		
		$params['controllerId'] = $controllerId;
		$contentKey=null;
		$params["authorizedToStock"]= Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE);
		$params['images'] = Document::getListDocumentsByIdAndType($id, $type, $contentKey, Document::DOC_TYPE_IMAGE);
		$controller->subTitle = "";
		echo $controller->renderPartial("gallery", $params);
    }
}