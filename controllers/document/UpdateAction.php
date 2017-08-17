<?php
class UpdateAction extends CAction {
	

	public function run() {
		
        if(strtolower($_SERVER['REQUEST_METHOD']) != 'post') {
            echo json_encode(array('result'=>false,'error'=>Yii::t("document","Error! Wrong HTTP method!")));
            exit;
        }
        $doc=Document::getById($_POST["id"]);
        if(!empty($doc)){
            if (Authorisation::canParticipate(Yii::app()->session["userId"], $doc["type"], $doc["id"])){
                $res = Document::update($_POST["id"], $_POST);
                if ($res) {
                    echo json_encode(array('result'=>true,
                                                "message"=>Yii::t("common","Document is well updated"),
                                                'data'=>$_POST
                                                ));
                }else 
                    echo json_encode(array('result'=>false,'msg'=>Yii::t("document","Something went wrong with the update!")));
            } else
                echo json_encode(array('result'=>false, "msg" => Yii::t("document","You are not allowed to delete this document !"), "id" => $_POST["id"]));
        } else
          echo json_encode(array('result'=>false, "msg" => Yii::t("document","Document doesn't exist !"), "id" => $_POST["id"]));
	}

}