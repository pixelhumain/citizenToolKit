<?php
class UploadAction extends CAction {
	

	public function run($dir,$folder=null,$ownerId=null,$input,$rename=false) {
		
        if(strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
            echo json_encode(array('result'=>false,'error'=>Yii::t("document","Error! Wrong HTTP method!")));
            exit;
        }

        if(array_key_exists($input,$_FILES) && $_FILES[$input]['error'] == 0 ) {
            $file = $_FILES[$input];
        } else {
            error_log("WATCH OUT ! - ERROR WHEN UPLOADING A FILE ! CHECK IF IT'S NOT AN ATTACK");
            echo json_encode(array('result'=>false,'msg'=>Yii::t("document","Something went wrong with your upload!")));
            exit;
        }
        
        $res = Document::checkFileRequirements($file, $dir, $folder, $ownerId, $input);
        if ($res["result"]) {
            $res = Document::uploadDocument($file, $res["uploadDir"],$input,$rename);
            if ($res["result"]) {
                Rest::json(array('result'=>true,
                                        "success"=>true,
                                        'name'=>$res["name"],
                                        'dir'=> $res["uploadDir"],
                                        'size'=> (int)filesize ($res["uploadDir"].$res["name"]) ));
                exit;
            }
        }
        
        Rest::json(array('result'=>false,'msg'=>Yii::t("document","Something went wrong with your upload!")));
    	exit;
	}

}