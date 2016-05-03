<?php
class ModerateAction extends CAction
{
    public function run()
    {
	    $controller=$this->getController();

        //Detail moderate one comment
        if(isset($_REQUEST['subAction']) && $_REQUEST['subAction'] == "consolidateModerateComment"){
            if(isset($_REQUEST['id'])){
                $comment = Comment::getById($_REQUEST['id']);
                $tmp = array();
                $tmp['text'] = @$comment['text'];
                if(isset($comment['reportAbuse']) && is_array($comment['reportAbuse'])){
                    foreach ($comment['reportAbuse'] as $user => $reason) {
                        if(isset($reason)){

                            //Consolidate reason
                            if(isset($tmp['reason'][$reason])){
                                $tmp['reason'][$reason] = $tmp['reason'][$reason] + 1;
                            }
                            else{
                                $tmp['reason'][$reason] = 1;
                            }

                            //details
                            $reporter = Person::getById($user);
                            $tmp['detail'][$user] = $reason." - Par ".@$reporter['name'].'('.@$reporter['email'].')';
                        }
                    }
                }
                Rest::json(array("result"=>true, "result"=>$tmp));
            }
            else{
                Rest::json(array("result"=>false, "msg"=>"Erreur Paramètre manquant"));  
            }
        } //Save moderate Action
        elseif(isset($_REQUEST['subAction']) && $_REQUEST['subAction'] == "saveModerate"){
            if(isset($_REQUEST['id']) && isset($_REQUEST['isAnAbuse'])){
                $comment = Comment::getById($_REQUEST['id']);
                if($comment){
                    $moderate = array(
                        'isAnAbuse' => $_REQUEST['isAnAbuse'],
                        'moderatedBy' => Yii::app()->session["userId"],
                        'date' => new MongoDate(time())
                    );
                    $res = Comment::updateField($_REQUEST['id'], "moderate", $moderate, Yii::app()->session["userId"]);
                    if(@$res["result"]){
                        Rest::json(array("result"=>true, "msg"=>"Ok, Modération enregistrée"));  
                    }
                    else{
                        Rest::json(array("result"=>false, "msg"=>"Erreur dans l'enregistrement de la modération"));  
                    }
                }
                else{
                     Rest::json(array("result"=>false, "msg"=>"Erreur comment inexistant"));  
                }
            }
            else{
                Rest::json(array("result"=>false, "msg"=>"Erreur Paramètre comment manquant"));  
            }       
        }//nothing
        else{
            Rest::json(array("result"=>false, "msg"=>"Erreur Paramètre action manquant"));  
        }
    }
}