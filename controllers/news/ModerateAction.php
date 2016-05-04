<?php
class ModerateAction extends CAction
{
    public function run()
    {
	    $controller=$this->getController();

        //Detail moderate one news
        if(isset($_REQUEST['subAction']) && $_REQUEST['subAction'] == "consolidateModerateNews"){
            if(isset($_REQUEST['id'])){
                $news = News::getById($_REQUEST['id']);
                $tmp = array();
                $tmp['text'] = @$news['text'];
                if(isset($news['reportAbuseReason']) && is_array($news['reportAbuseReason'])){
                    foreach ($news['reportAbuseReason'] as $listReason) {
                        foreach ($listReason as $user => $reason) {
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
                }
                Rest::json(array("result"=>true, "result"=>$tmp));
            }
            else{
                Rest::json(array("result"=>false, "msg"=>"Erreur Paramètre manquant"));  
            }
        } //Save moderate Action
        elseif(isset($_REQUEST['subAction']) && $_REQUEST['subAction'] == "saveModerate"){
            if(isset($_REQUEST['id']) && isset($_REQUEST['isAnAbuse'])){
                //Test exist
                $news = News::getById($_REQUEST['id']);
                if($news){
                    $moderate = array(
                        'isAnAbuse' => $_REQUEST['isAnAbuse'],
                        'moderatedBy' => Yii::app()->session["userId"],
                        'date' => new MongoDate(time())
                    );
                    $res = News::updateField($_REQUEST['id'], "moderate", $moderate, Yii::app()->session["userId"]);
                    if(@$res["result"]){

                        Notification::moderateNews(Yii::app()->session["userId"],$news['author']);

                        Rest::json(array("result"=>true, "msg"=>"Ok, Moderation enregistrée"));  
                    }
                    else{
                        Rest::json(array("result"=>false, "msg"=>"Erreur dans l'enregistrement de la modération"));  
                    }
                }
                else{
                    Rest::json(array("result"=>false, "msg"=>"Erreur news inexistante"));  
                }
            }
            else{
                Rest::json(array("result"=>false, "msg"=>"Erreur Paramètre manquant"));  
            }
        }
        elseif(isset($_REQUEST['subAction']) && $_REQUEST['subAction'] == "getNextIdToModerate"){
            $where = array(
                "reportAbuse"=> array('$exists'=>1)
                ,"moderate.isAnAbuse" => array('$exists'=>0)
                ,"target.id" => array('$exists'=>1)
                ,"target.type" => array('$exists'=>1)
                ,"scope.type" => array('$exists'=>1)
            );
            $params =  PHDB::find('news',$where, array('_id' => 1), 1);
            if(isset($params) && count($params)){
              $params = array_pop($params);
              Rest::json(array("result"=>true, "msg"=>"il y a des news à modérer", "newsId" => (string)@$params['_id']));  
            }
            else{
                Rest::json(array("result"=>false, "msg"=>"Plus aucune news à modérer"));  
            }
        }//nothing
        else{
            Rest::json(array("result"=>false, "msg"=>"Erreur Paramètre manquant"));  
        }
    }
}