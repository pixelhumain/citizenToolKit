<?php
class IndexAction extends CAction
{
    public function run($id, $type)
    {
        $controller=$this->getController();

		$item = PHDB::findOne( $type ,array("_id"=>new MongoId($id)));
		$params = array();
		$params["itemId"] = $id;
		$params['itemType'] = $type;
		
		//TODO SBAR - it's not beautifull. Refactor soon
		switch ($type) {
			case Person::COLLECTION:
				$controllerId = "person";
				break;
			case Organization::COLLECTION:
				$controllerId = "organization";
				break;
			case Project::COLLECTION:
				$controllerId = "project";
				break;
			case Event::COLLECTION:
				$controllerId = "event";
				break;
			default:
				throw new CTKException("Impossible to manage this type ".$type);
				break;
		}

		$params['controllerId'] = $controllerId;
		$params['images'] = Document::getListDocumentsByContentKey($id, $controllerId, Document::DOC_TYPE_IMAGE);

		$controller->title = $item["name"]."'s Gallery";
		$controller->subTitle = "";
		$controller->render("gallery", $params);
    }
}