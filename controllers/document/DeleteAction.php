<?php
class DeleteAction extends CAction {
	

	public function run($dir,$type) {
		if (! Person::logguedAndValid()) {
			echo json_encode(array('result'=>false,'error'=>Yii::t("common","Please Log in order to update document !")));
			return;
		}

		if ( @$_POST["path"] == "communevent" ){
			// Method for Communevent
			Document::removeDocumentCommuneventByObjId($_POST["docId"], Yii::app()->session["userId"]);
			if(@$_POST["source"] && $_POST["source"]=="gallery")
				News::removeNewsByImageId($_POST["docId"]);
			
		} else {
			$res = Document::removeDocumentById($_POST["docId"], Yii::app()->session["userId"]);
			
	    }
	    Rest::json($res);
	}
}