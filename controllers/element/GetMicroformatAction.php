<?php

class GetMicroformatAction extends CAction {
	/**
	* Dashboard Organization
	*/
    public function run() { 
    	 //Yii::app()->session["userLang"] = "fr";
        if(isset($_POST["key"])){
            $microformat = PHDB::findOne(PHType::TYPE_MICROFORMATS, array( "key"=>$_POST["key"] ));
            if(!empty($microformat)){
                $_POST["microformat"] = $microformat;
                $html = "";
                $title = "Formulaire";
                if($_POST["microformat"]){
                    //clef trouvé dans microformats
                    if(isset($_POST["microformat"]["jsonSchema"]["title"]))
                        $title = $_POST["microformat"]["jsonSchema"]["title"];
                    $tpl = (isset($_POST["microformat"]["template"])) ? $_POST["microformat"]["template"] : "dynamicallyBuild";
                    $html .= $this->renderPartial($tpl,$_POST,true);
                } else {
                    //clef pas trouvé dans microformats
                    $html .= $this->renderPartial($_POST["template"],$_POST,true);
                }
                echo json_encode( array("result"=>true,"title"=>$_POST["key"],"content"=>$html));
            } else 
                echo json_encode( array("result"=>false,"key"=>$_POST["key"],"msg"=>"This Microformat doesn't exist."));
        } else 
            echo json_encode( array("result"=>false,"title"=>$title,"content"=>"Votre ne peut aboutir"));
	}
}

?>