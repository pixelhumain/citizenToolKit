<?php
class WikipediaAction extends CAction {

    public function run($url=null, $wikidataID=null) {

		$res = Convert::convertWikiToPh($url, $wikidataID);

  		if (isset($res)) {
			Rest::json($res);
		}

		Yii::app()->end();
	}
}

?>