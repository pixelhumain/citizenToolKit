<?php
class DeleteAction extends CAction {
	

	public function run($dir,$type) {
		$filepath = Yii::app()->params['uploadDir'].$dir.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$_POST['parentId'].DIRECTORY_SEPARATOR.$_POST['name'];
        if(isset(Yii::app()->session["userId"]) && file_exists ( $filepath ))
        {
            if (unlink($filepath))
            {
                Document::removeDocumentById($_POST['docId']);
                echo json_encode(array('result'=>true, "msg" => Yii::t("document","Document deleted",null,Yii::app()->controller->module->id)));
            }
            else
                echo json_encode(array('result'=>false,'error'=>Yii::t("document","Something went wrong!",null,Yii::app()->controller->module->id), "filepath" => $filepath));
        } 
        else 
        {
            $doc = Document::getById( $_POST['docId'] );
            if( $doc )
                Document::removeDocumentById($_POST['docId']);
            echo json_encode(array('result'=>false,'error'=>Yii::t("document","Something went wrong!",null,Yii::app()->controller->module->id),"filepath"=>$filepath));
        }
	}
}