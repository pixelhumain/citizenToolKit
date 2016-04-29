<?php
class DeleteAction extends CAction {
	

	public function run($dir,$type) {
		// Path to orginal Image
		$filepath = Yii::app()->params['uploadDir'].$dir.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$_POST['parentId'].DIRECTORY_SEPARATOR;
		if(!empty($_POST["path"]))
				$filepath.=$_POST["path"].DIRECTORY_SEPARATOR;
		$filepath.=$_POST['name'];
		// Path to the thumb
		$filepathThumb = Yii::app()->params['uploadDir'].$dir.DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$_POST['parentId'].DIRECTORY_SEPARATOR;
		if(!empty($_POST["path"]))
				$filepathThumb.=$_POST["path"].DIRECTORY_SEPARATOR.Document::GENERATED_IMAGES_FOLDER.DIRECTORY_SEPARATOR.$_POST['name'];
		else{
			$pathThumbProfil=Document::GENERATED_IMAGES_FOLDER.DIRECTORY_SEPARATOR.IMG_PROFIL_RESIZED;
			$pathThumbMarker=Document::GENERATED_IMAGES_FOLDER.DIRECTORY_SEPARATOR.IMG_PROFIL_MARKER;
			$filepathThumbProfil=$filepathThumb.$pathThumbProfil;
			$filepathThumbMarker=$filepathThumb.$pathThumbMarker;
		}
		
        if(isset(Yii::app()->session["userId"]) && file_exists ( $filepath ))
        {
            if (unlink($filepath))
            {
	            $documentId=$_POST['docId'];
	           	if(!empty($_POST["path"])){
	            	if(file_exists ( $filepathThumb ))
	            		unlink($filepathThumb);
	            }else{
		            if(file_exists ( $filepathThumbProfil ))
	            		unlink($filepathThumbProfil);
	            	if(file_exists ( $filepathThumbMarker ))
	            		unlink($filepathThumbMarker);
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