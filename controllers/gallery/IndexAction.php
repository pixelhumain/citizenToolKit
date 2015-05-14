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
		
		$controller->title = $item["name"]."'s Gallery";
		$controller->subTitle = "";
		$controller->render("gallery", $params);
    }
}