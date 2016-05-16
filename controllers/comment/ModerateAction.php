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
                   $details = array(
                        'isAnAbuse' => $_REQUEST['isAnAbuse'],
                        'date' => new MongoDate(time())
                    );
                    // $map["moderate".".".Yii::app()->session["userId"]] = $addToMap ;
                    // $map["moderateCount"] = @$news["moderateCount"]+1;
                    // $res = PHDB::update ( Comment::COLLECTION , array( "_id" => new MongoId($_REQUEST["id"])), array( '$set' => $map));
                    $res = Action::addAction(Yii::app()->session["userId"] , $_REQUEST['id'], Comment::COLLECTION, Action::ACTION_MODERATE, false, false, $details);
                    
                    if($res['userActionSaved']){
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