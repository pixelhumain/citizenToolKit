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
                $tmp['reason'] = array();
                if(isset($news['reportAbuse']) && is_array($news['reportAbuse'])){
                    foreach ($news['reportAbuse'] as $user => $detailReason) {
                        // foreach ($listReason as $user => $reason) {
                            if(isset($detailReason['reason'])){
                                $reason = $detailReason['reason'];
                                $comment = "";
                                if(isset($detailReason['comment']))$comment = " (".$detailReason['comment']." )";
                                
                                //Consolidate reason
                                if(isset($tmp['reason'][$reason])){
                                    $tmp['reason'][$reason] = $tmp['reason'][$reason] + 1;
                                }
                                else{
                                    $tmp['reason'][$reason] = 1;
                                }

                                //details
                                $reporter = Person::getById($user);
                                $tmp['detail'][$user] = date('Y-m-d h:i:s', $detailReason['date']->sec)." - ".@$reporter['name'].' : '.$reason.$comment;
                            }
                        // }
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
                    /**** SAVE ACTION ****/
                    $details = array(
                                'isAnAbuse' => $_REQUEST['isAnAbuse']
                    );

                    $resAction = Action::addAction(Yii::app()->session["userId"] , $_REQUEST['id'], "news", Action::ACTION_MODERATE, false, false, $details);

                    //Save => OK
                    if($resAction['userActionSaved']){

                        /**** ISANABUSE ? ****/
                        if((@$news["moderateCount"]+$resAction['inc']) >= 3){
                            //Cosolidate all moderation of this news
                            $totalAbuse = 0;
                            if($_REQUEST['isAnAbuse'] == "true")$totalAbuse = 1;
                            if(isset($news['moderate']))foreach ($news['moderate'] as $user => $oneModeration) {
                                if($oneModeration['isAnAbuse'] == "true")$totalAbuse += 1;
                            }

                            //Answer
                            if($totalAbuse == 0){
                                $pourcentage = 0;
                            }else{
                                $pourcentage = round(($totalAbuse / (@$news["moderateCount"]+$resAction['inc']))*100);
                            }
                            $news["isAnAbuse"] = $isAnAbuse["isAnAbuse"] = false;
                            if($pourcentage > 49)$isAnAbuse["isAnAbuse"] = true;
                            $res = PHDB::update ( News::COLLECTION , array( "_id" => new MongoId($_REQUEST["id"])), array( '$set' => $isAnAbuse));

                            /**** NOTIFICATION ****/
                            Notification::moderateNews($news);
                        }

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
            $params = News::getNewsToModerate(null,1);
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