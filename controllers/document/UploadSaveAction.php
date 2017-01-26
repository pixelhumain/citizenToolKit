<?php
class UploadSaveAction extends CAction {
	

	public function run($dir,$folder=null,$ownerId=null,$input,$rename=false) {
		
        $res = array('result'=>false, 'msg'=>Yii::t("document","Something went wrong with your upload!"));
        if (Person::logguedAndValid()) 
        {
            if(strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
                $res = array('result'=>false,'error'=>Yii::t("document","Error! Wrong HTTP method!"));
            }

            //{"result":false,"msg":"Le chargement du document ne s'est pas deroul\u00e9 correctement",
            //"file":{ "qqfile":{"name":"compo.jpg","type":"","tmp_name":"","error":6,"size":0}}}
            if(array_key_exists($input,$_FILES) && $_FILES[$input]['error'] == 0 ) {
                $file = $_FILES[$input];
            } else {
                error_log("WATCH OUT ! - ERROR WHEN UPLOADING A FILE ! CHECK IF IT'S NOT AN ATTACK");
                $res = array('result'=>false,'msg'=>Yii::t("document","Something went wrong with your upload!"));
            }
            $res['file'] = $file;   
                        
            $res = Document::checkFileRequirements($file, $dir, $folder, $ownerId, $input);
            if ($res["result"]) {
                $res = Document::uploadDocument($file, $res["uploadDir"],$input,$rename);
                if ($res["result"]) {
                    $res = array('resultUpload'=>true,
                                    "success"=>true,
                                    'name'=>$res["name"],
                                    'dir'=> $res["uploadDir"],
                                    'size'=> (int)filesize ($res["uploadDir"].$res["name"]) );
                }
            }
            $res2 = array();
            
            if($res["resultUpload"]){
                
                    $params = array(
                        "id" => $ownerId,
                        "type" => $folder,
                        "folder" => $folder."/".$ownerId,
                        "moduleId" => "communecter",
                        "name" => $res["name"],
                        "size" => (int) $res['size'],
                        "contentKey" => (@$_POST["contentKey"]) ? $_POST["contentKey"]:"profil",
                        "author" => Yii::app()->session["userId"]
                    );

                    if(@$_POST["parentType"])
                        $params["parentType"] = $folder;
                    if(@$_POST["parentId"])
                        $params["parentId"] = $ownerId;           
                    if(@$_POST["formOrigin"])
                        $params["formOrigin"] = $_POST["formOrigin"];
                    $res2 = Document::save($params);
                
            }

        } else 
            $res2 = array("result" => false, "msg" => Yii::t("common","Please Log in order to update document !"));
            
        
        $res = array_merge($res,$res2 );
        return Rest::json($res);
	}

}