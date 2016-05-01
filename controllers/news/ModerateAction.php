<?php
class ModerateAction extends CAction
{
    public function run()
    {
	    $controller=$this->getController();

        //Detail moderate one news
        if(isset($_REQUEST['consolidateModerateNews']) && $_REQUEST['consolidateModerateNews'] == true){
            if(isset($_REQUEST['id'])){
                $news = News::getById($_REQUEST['id']);
                $tmp = array();
                $tmp['text'] = @$news['text'];
                if(isset($news['reportAbuseReason']) && is_array($news['reportAbuseReason'])){
                    foreach ($news['reportAbuseReason'] as $listReason) {
                        foreach ($listReason as $user => $reason) {
                            if(isset($reason)){
                                if(isset($tmp[$reason])){
                                    $tmp[$reason] = $tmp[$reason] + 1;
                                }
                                else{
                                    $tmp[$reason] = 1;
                                }
                            }
                        }
                    }
                }
                Rest::json(array("result"=>true, "result"=>$tmp));
            }
            else{
                Rest::json(array("result"=>false, "msg"=>"Erreur Paramètre manquant"));  
            }
        } //Save moderate Action
        elseif(isset($_REQUEST['saveModerate']) && $_REQUEST['saveModerate'] == true){
            if(isset($_REQUEST['id']) && isset($_REQUEST['isAnAbuse'])){
                $moderate = array(
                    'isAnAbuse' => $_REQUEST['isAnAbuse'],
                    'moderatedBy' => Yii::app()->session["userId"],
                    'date' => new MongoDate(time())
                );
                $res = News::updateField($_REQUEST['id'], "moderate", $moderate, Yii::app()->session["userId"]);
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
        }//nothing
        else{
            Rest::json(array("result"=>false, "msg"=>"Erreur Paramètre manquant"));  
        }
    }
}