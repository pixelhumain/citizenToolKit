<?php
class DeleteAction extends CAction {
	

	public function run($dir,$type) {
		$filepath = Yii::app()->params['uploadDir'].$dir.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$_POST['parentId'].DIRECTORY_SEPARATOR.$_POST['name'];
        if(isset(Yii::app()->session["userId"]) && file_exists ( $filepath ))
        {
            if (unlink($filepath))
            {
                Document::removeDocumentById($_POST['docId']);
                echo json_encode(array('result'=>true, "msg" => "Document bien supprimé"));
            }
            else
                echo json_encode(array('result'=>false,'error'=>'Something went wrong!', "filepath" => $filepath));

            if(isset($_POST['parentId']) && isset($_POST['parentType']) && isset($_POST['pictureKey']) && isset($_POST['path'])){
                Document::setImagePath($_POST['parentId'], $_POST['parentType'], "", $_POST['pictureKey']);
            }
        } 
        else 
        {
            $doc = Document::getById( $_POST['docId'] );
            if( $doc )
                Document::removeDocumentById($_POST['docId']);
            echo json_encode(array('result'=>false,'error'=>'Something went wrong!',"filepath"=>$filepath));
        }
	}
}