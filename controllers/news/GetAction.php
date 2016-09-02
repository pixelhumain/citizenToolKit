<?php
class GetAction extends CAction {
    
    public function run($id = null, $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null) {
		$controller=$this->getController();
		// Get format
		$bindMap = TranslateCommunecter::$dataBinding_news;

    $result = Api::getData($bindMap, $format, News::COLLECTION, $id,$limit, $index, $tags, $multiTags, $key, $insee);

		Rest::json($result);
		Yii::app()->end();
    }
}