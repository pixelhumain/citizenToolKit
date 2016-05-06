<?php
class IndexAction extends CAction
{
    public function run($id, $type)
    {
        $controller=$this->getController();

		//$item = PHDB::findOne( $type ,array("_id"=>new MongoId($id)));
		$params = array();
		$params["itemId"] = $id;
		$params['itemType'] = $type;
		
		//TODO SBAR - it's not beautifull. Refactor soon
		switch ($type) {
			case Person::COLLECTION:
				$controllerId = "person";
				$params["person"] = Person::getPublicData($id);
				break;
			case Organization::COLLECTION:
				$controllerId = "organization";
				$params["organization"] = Organization::getPublicData($id);
				break;
			case Project::COLLECTION:
				$controllerId = "project";
				$params["project"] = Project::getPublicData($id);
				break;
			case Event::COLLECTION:
				$controllerId = "event";
				$params["event"] = Event::getPublicData($id);
				break;
			default:
				throw new CTKException("Impossible to manage this type ".$type);
				break;
		}

		if(isset(Yii::app()->session["userId"]))
			$params["canEdit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $id);
		
		$params['controllerId'] = $controllerId;
		$contentKey=null;
		//$params['images'] = Document::getListDocumentsByContentKey($id, $controllerId, Document::DOC_TYPE_IMAGE);
		$params['images'] = Document::getListDocumentsByIdAndType($id, $type, $contentKey, Document::DOC_TYPE_IMAGE);
		//$controller->title = $item["name"]."'s Gallery";
		$controller->subTitle = "";
		echo $controller->renderPartial("gallery", $params);
		//$controller->render("gallery", $params);
    }
}