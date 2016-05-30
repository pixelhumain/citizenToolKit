<?php
class DeleteAction extends CAction {
	

	public function run($dir,$type) {
		if ($_POST["path"]=="communevent"){
			// Method for Communevent
			Document::removeDocumentCommuneventByObjId($_POST["docId"]);
			if(@$_POST["source"] && $_POST["source"]=="gallery")
				News::removeNewsByImageId($_POST["docId"]);
			echo json_encode(array('result'=>true, "msg" => Yii::t("document","Image deleted")));
		} 
		else {
			// Path to orginal Image
			$filepath = Yii::app()->params['uploadDir'].$dir.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$_POST['parentId'].DIRECTORY_SEPARATOR;
			if(!empty($_POST["path"]))
					$filepath.=$_POST["path"].DIRECTORY_SEPARATOR;
			$filepath.=$_POST['name'];
			// Path to the thumb
			$filepathThumb = Yii::app()->params['uploadDir'].$dir.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$_POST['parentId'].DIRECTORY_SEPARATOR;
			if(!empty($_POST["path"]))
					$filepathThumb.=$_POST["path"].DIRECTORY_SEPARATOR.Document::GENERATED_IMAGES_FOLDER.DIRECTORY_SEPARATOR.$_POST['name'];
					
	        if(isset(Yii::app()->session["userId"]) && file_exists ( $filepath ))
	        {
	            if (unlink($filepath))
	            {
		            $documentId=$_POST['docId'];
		           	if(!empty($_POST["path"])){
		            	if(file_exists ( $filepathThumb ))
		            		unlink($filepathThumb);
		            }
		            Document::removeDocumentById($documentId);
	                echo json_encode(array('result'=>true, "msg" => Yii::t("document","Document deleted"), "id" => $documentId));
	            }
	            else
	                echo json_encode(array('result'=>false,'msg'=>Yii::t("common","Something went wrong!"), "filepath" => $filepath));
	        } 
	        else 
	        {
	            $doc = Document::getById( $_POST['docId'] );
	            if( $doc )
	                Document::removeDocumentById($_POST['docId']);
	            echo json_encode(array('result'=>false,'error'=>Yii::t("common","Something went wrong!"),"filepath"=>$filepath));
	        }
	    }
	}
}