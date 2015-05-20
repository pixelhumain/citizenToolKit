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
		
		//Delete the last 's' of the type to get the contentKey
		$contentKey = substr($type,0,-1);
		$params['images'] = Document::getListDocumentsByContentKey($id, $contentKey, Document::DOC_TYPE_IMAGE);
		$params['controllerId'] = $contentKey;

		$controller->title = $item["name"]."'s Gallery";
		$controller->subTitle = "";
		$controller->render("gallery", $params);
    }
}