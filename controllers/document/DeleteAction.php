<?php
class DeleteAction extends CAction {
	

	public function run($dir,$type,$id) {
		if (! Person::logguedAndValid()) {
			echo json_encode(array('result'=>false,'error'=>Yii::t("common","Please Log in order to update document !")));
			return;
		}

		if ($_POST["path"]=="communevent"){
			// Method for Communevent
			Document::removeDocumentCommuneventByObjId($_POST["docId"], Yii::app()->session["userId"]);
			if(@$_POST["source"] && $_POST["source"]=="gallery")
				News::removeNewsByImageId($_POST["docId"]);
			echo json_encode(array('result'=>true, "msg" => Yii::t("document","Image deleted")));
		} else {
			if (Authorisation::canParticipate(Yii::app()->session["userId"], $type, $id)){
				foreach($_POST["ids"] as $data){
					Document::removeDocumentById($data);
				}
				echo json_encode(array('result'=>true, "msg" => Yii::t("document","Image deleted")));
			} else {
		    	echo json_encode(array('result'=>false, "msg" => Yii::t("document","You are not allowed to delete this document !"), "id" => $id));
			}
	    }
	}
}