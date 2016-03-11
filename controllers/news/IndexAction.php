<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null, $date = null, $streamType="news")
    {
        $controller=$this->getController();
        $controller->title = "Timeline";
        $controller->subTitle = "NEWS comes from everywhere, and from anyone.";
        $controller->pageTitle = "Communecter - Timeline Globale";
        $news = array(); 
        if(!function_exists("array_msort")){
			function array_msort($array, $cols)
			{
			    $colarr = array();
			    foreach ($cols as $col => $order) {
			        $colarr[$col] = array();
			        foreach ($array as $k => $row) { $colarr[$col]['_'.$k] = strtolower($row[$col]); }
			    }
			    $eval = 'array_multisort(';
			    foreach ($cols as $col => $order) {
			        $eval .= '$colarr[\''.$col.'\'],'.$order.',';
			    }
			    $eval = substr($eval,0,-1).');';
			    eval($eval);
			    $ret = array();
			    foreach ($colarr as $col => $arr) {
			        foreach ($arr as $k => $v) {
			            $k = substr($k,1);
			            if (!isset($ret[$k])) $ret[$k] = $array[$k];
			            $ret[$k][$col] = $array[$k][$col];
			        }
			    }
			    return $ret;
			
			}
		}
        //mongo search cmd : db.news.find({created:{'$exists':1}})	
		$params = array();
		if (!isset($id)){
			if($type!="pixels"){
				if($type=="city"){
					$id=$_GET["insee"];
				}
				else{
					if(@$_GET["id"])
						$id=$_GET["id"];
					else 
						$id = Yii::app() -> session["userId"] ;
				}
			} else {
				$id="";
			}
		}
		if(@$date && $date != null){
			$date = $date;
		}
		else{
			$date=time();
		}
		$date=new MongoDate($date);
		$news=array();
		if(!@$type || empty($type))
			$type = Person::COLLECTION;
		
		$params["type"] = $type; 
        if( $type == Project::COLLECTION ) {
            $project = Project::getById($id);
            if(@Yii::app()->session["userId"] && @$project["links"]["contributors"][Yii::app()->session["userId"]] && !@$project["links"]["contributors"][Yii::app()->session["userId"]][TO_BE_VALIDATED])
            	$params["canPostNews"] = true;
            $params["project"] = $project; 
        } 
        else if( $type == Person::COLLECTION ) {
            $person = Person::getById($id);
            if (@Yii::app()->session["userId"])
				$params["canPostNews"] = true;
            $params["person"] = $person; 
        } 
        else if( $type == Organization::COLLECTION) {
            $organization = Organization::getById($id);
            if(@Yii::app()->session["userId"] && @$organization["links"]["members"][Yii::app()->session["userId"]] && !@$organization["links"]["members"][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])
            	$params["canPostNews"] = true;
            $params["organization"] = $organization; 
        }
        else if( $type == Event::COLLECTION ) {
            $event = Event::getById($id);
            $onclick = "showAjaxPanel( '/organization/detail/id/".$id."', 'EVENT DETAIL : ".$event["name"]."','calendar' )";
	        $entry = array('tooltip' => "Back to Event Details",
                            "iconClass"=>"fa fa-calendar",
                            "href"=>"<a  class='tooltips  btn btn-default' href='#' onclick=\"".$onclick."\"");
            $params["event"] = $event; 
        }
        else if ($type=="city"){
	        if (@Yii::app()->session["userId"])
				$params["canPostNews"] = true;
        }
		else if ($type=="pixels"){
			$params["canPostNews"] = true;
		}
		if ($streamType == "news"){
			if($type == "citoyens") {
				$authorFollowedAndMe=[];
				array_push($authorFollowedAndMe,array("author"=>$id));
				if(@$person["links"]["knows"] && !empty($person["links"]["knows"])){
					foreach ($person["links"]["knows"] as $key => $data){
						array_push($authorFollowedAndMe,array("id"=>$key, "type" => "citoyens"));
					}
				}
				if(@$person["links"]["memberOf"] && !empty($person["links"]["memberOf"])){
					foreach ($person["links"]["memberOf"] as $key => $data){
						array_push($authorFollowedAndMe,array("id"=>$key, "type" => "organizations"));
					}
				}
				if(@$person["links"]["projects"] && !empty($person["links"]["projects"])){
					foreach ($person["links"]["projects"] as $key => $data){
						array_push($authorFollowedAndMe,array("id"=>$key, "type" => "projects"));
					}
				}
				if(@$person["links"]["follows"] && !empty($person["links"]["follows"])){
					foreach ($person["links"]["follows"] as $key => $data){
						$followNews=array("id"=>$key, "scope.type" => "public", "type" => "");
						$followActivity=array("type" => "activityStream");
						if($data["type"]==Project::COLLECTION){
							$followNews["type"] = $data["type"];
							$followActivity=array("type"=>"activityStream","target.id" => $key,"target.objectType"=>$data["type"]);
							//$followActivity["target"]["id"]=$key;
							//$followActivity=array("type");["target"]["objectType"]=$data["type"];
						}if($data["type"]==Person::COLLECTION){
							$followNews["type"] = "citoyens";
							$followActivity=array("type"=>"activityStream","target.id" => $key,"target.objectType"=>$data["type"]);
							//$followActivity["target"]["id"]=$key;
							//$followActivity["target"]["objectType"]=$data["type"];
						}if($data["type"]==Organization::COLLECTION){
							$followActivity["target"]["id"]=$key;
							$followActivity=array("type"=>"activityStream","target.id" => $key,"target.objectType"=>$data["type"]);
							//$followActivity["target"]["objectType"]=$data["type"];
							//$followNews["type"] = "organizations";
						}
						array_push($authorFollowedAndMe,$followNews);
						array_push($authorFollowedAndMe,$followActivity);
					}
				}
				if(@$person["address"]["codeInsee"])
					array_push($authorFollowedAndMe, array("scope.cities." => $person["address"]["codeInsee"],"type" => "activityStream"));
		        $where = array('$and' => array(
								array('$or'=> 
										$authorFollowedAndMe
								),
								array("type" => array('$ne' => "pixels")),
								array('created' => array(
										'$lt' => $date
									)
								),
				        	)	
				        );
				      // print_r($where);
			}
			else if($type == "organizations" || $type == "projects"){
				$where = array('$and' => array(
						array("text" => array('$exists'=>1)),
						array("type"=> $type),
						array("id"=> $id),
						array('created' => array(
								'$lt' => $date
							)
						),
		        	)	
				);
			}
			else if ($type == "pixels"){
				$where = array('$and' => array(
						array("text" => array('$exists'=>1)),
						array("type"=> $type),
						array('created' => array(
								'$lt' => $date
							)
						),
		        	)	
				);
			}
			else if($type == "city"){
				//array('$in' => array($id))
				$where = array('$and' => array(
						array("scope.cities.codeInsee" => $id ),
						array("type" => array('$ne' => "pixels")),
						array('created' => array(
								'$lt' => $date
							)
						),
		        	)	
				);
			}
			$news=News::getNewsForObjectId($where,array("created"=>-1),$type);
		}
        //TODO : get all notifications for the current context
        /*else {
			if ( $type == Project::COLLECTION ){ 
				$paramProject = array(
								'$and' => array(
									array("timestamp" => array('$lt' => $date)),
									array("target.objectType"=>$type,"target.id"=>$id),
									array('$or' => 
										array(
											array('$and'=> 
												array(
													array("verb"=> ActStr::VERB_CREATE), 
													array('$or' => 
														array(
															array("object.objectType" => Need::COLLECTION), 
															array("object.objectType" => Event::COLLECTION), 
															array("object.objectType" => Gantt::COLLECTION)
														)
													)
												)
											), 									
											array("verb" => ActStr::VERB_INVITE)
										)	
									)	
								)
							);
				$newsProject=ActivityStream::getActivtyForObjectId($paramProject,array("timestamp"=>-1));
				if (@$newsProject){
					foreach ($newsProject as $key => $data){
						if($data["verb"]==ActStr::VERB_CREATE){
							if($data["object"]["objectType"]==Need::COLLECTION){
								$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_NEED);
							}
							else if($data["object"]["objectType"]==Event::COLLECTION){
								$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_EVENT);
							}
							else if($data["object"]["objectType"]==Gantt::COLLECTION){
								$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_TASK);
							}	
						}
						else if ($data["verb"]==ActStr::VERB_INVITE){
							$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CONTRIBUTORS);
						}
						$news[$key]=$newsObject;	
					}
				}
			}
			if ( $type == Person::COLLECTION ){ 
				//$date=new MongoDate($date);
				// GET ACTIVITYSTREAM FROM OTHER WITH SAME CODEINSEE
				$person=Person::getById(Yii::app()->session["userId"]);
				$paramInsee = array(
								'$and' => array(
									array("verb"=> ActStr::VERB_CREATE), 
									array('$or' => array(
										array("object.objectType" => Project::COLLECTION), 
										array("object.objectType" => Event::COLLECTION), 
										//array("object.objectType" => Need::COLLECTION), 
										array("object.objectType" => Organization::COLLECTION)
										)
									), 
									array("codeInsee" => $person["address"]["codeInsee"]),
									array("timestamp" => array('$lt' => $date))
								)
				);
				$newsLocality=ActivityStream::getActivtyForObjectId($paramInsee,array("timestamp"=>-1));
				foreach ($newsLocality as $key => $data){
					if($data["object"]["objectType"]==Project::COLLECTION){
						$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_PROJECT);
					}
					else if($data["object"]["objectType"]==Event::COLLECTION){
						$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_EVENT);
					}
					else if($data["object"]["objectType"]==Organization::COLLECTION){
						$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_ORGANIZATION);
					}
					$news[$key]=$newsObject;
				}
			}
			if ( $type == Organization::COLLECTION ){ 
				$paramOrganization = array(
								'$and' => array(
									array("timestamp" => array('$lt' => $date)),
									array("target.objectType"=>Organization::COLLECTION,"target.id"=>$id),
									array('$or' => 
										array(
											array('$and'=> 
												array(
													array("verb"=> ActStr::VERB_CREATE), 
													array('$or' => 
														array(
															array("object.objectType" => Need::COLLECTION), 
															array("object.objectType" => Project::COLLECTION), 
															array("object.objectType" => Gantt::COLLECTION)
														)
													)
												)
											), 									
											array("verb" => ActStr::VERB_JOIN)
										)	
									)
								)
							);
				$newsOrganization=ActivityStream::getActivtyForObjectId($paramOrganization,array("timestamp"=>-1));
				if (@$newsOrganization){
					foreach ($newsOrganization as $key => $data){
						if($data["verb"]==ActStr::VERB_CREATE){
							if($data["object"]["objectType"]==Project::COLLECTION){
$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_PROJECT);
							}
							else if($data["object"]["objectType"]==Need::COLLECTION){
								$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_NEED);
							}
							else if($data["object"]["objectType"]==Event::COLLECTION){
								$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_CREATE_EVENT);
							}
						}
						else if ($data["verb"]==ActStr::VERB_JOIN){
$newsObject=NewsTranslator::convertToNews($data,NewsTranslator::NEWS_JOIN_ORGANIZATION);
						}
						$news[$key]=$newsObject;	
					}
				}
			}
		}*/

		$news = array_msort($news, array('created'=>SORT_DESC));
        //TODO : reorganise by created date
		$params["news"] = $news; 
		$params["tags"] = Tags::getActiveTags();
		$params["contextParentType"] = $type; 
		$params["contextParentId"] = $id;
		$params["userCP"] = Yii::app()->session['userCP'];
		$params["limitDate"] = end($news);
								//print_r($params["news"]);
								
		if(Yii::app()->request->isAjaxRequest){
			if (!@$_GET["isFirst"]){
				 echo $controller->renderPartial("index", $params,true);
	       } else{
				//
				echo json_encode($params);
	      }
	    }
	    else
			$controller->render( "index" , $params  );
    }
}