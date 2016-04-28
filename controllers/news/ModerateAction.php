<?php
class ModerateAction extends CAction
{
    public function run()
    {
	    $controller=$this->getController();
        if(isset($_POST['id']) && isset($_POST['isAnAbuse'])){
            // $news = News::getById($_POST['id']);
            $moderate = array(
                'isAnAbuse' => $_POST['isAnAbuse'],
                'moderatedBy' => Yii::app()->session["userId"],
                'date' => new MongoDate(time())
            );
            $res = News::updateField($_POST['id'], "moderate", $moderate, Yii::app()->session["userId"]);
            if(@$res["result"]){
                Rest::json(array("result"=>true, "msg"=>"Ok, Moderation enregistrée"));  
            }
            else{
                Rest::json(array("result"=>false, "msg"=>"Erreur dans l'enregistrement de la modération"));  
            }
        }
        else{
            Rest::json(array("result"=>false, "msg"=>"Erreur Paramètre manquant"));  
        }       
    }
}