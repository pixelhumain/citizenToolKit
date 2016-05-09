<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null, $date = null, $viewer=null,$streamType="news")
    {
    	$controller=$this->getController();
        $controller->title = "Timeline";
        $controller->subTitle = "NEWS comes from everywhere, and from anyone.";
        $controller->pageTitle = "Communecter - Timeline Globale";
        $news = array(); 
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
            if(@Yii::app()->session["userId"]){
            	$params["canPostNews"] = true;
                if(@$project["links"]["contributors"][Yii::app()->session["userId"]] && !@$project["links"]["contributors"][Yii::app()->session["userId"]][TO_BE_VALIDATED])
            	$params["canManageNews"] = true;
            }
            $params["project"] = $project; 
        } 
        else if( $type == Person::COLLECTION ) {
            $person = Person::getById($id);
            if (@Yii::app()->session["userId"]){
				$params["canPostNews"] = true;
				if (Yii::app()->session["userId"]==$id){
					$params["canManageNews"]=true;
				}
			}
            $params["person"] = $person; 
        } 
        else if( $type == Organization::COLLECTION) {
            $organization = Organization::getById($id);
            if(@Yii::app()->session["userId"]){
				$params["canPostNews"] = true;
            	if (@$organization["links"]["members"][Yii::app()->session["userId"]] && !@$organization["links"]["members"][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])
            		$params["canManageNews"] = true;
			}	
            $params["organization"] = $organization; 
        }
        else if( $type == Event::COLLECTION ) {
            $event = Event::getById($id);
            if((@Yii::app()->session["userId"] && @$event["links"]["attendees"][Yii::app()->session["userId"]] && !@$event["links"]["attendees"][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED]) ||
            	(@Yii::app()->session["userId"] && @$event["links"]["organizer"][Yii::app()->session["userId"]] && !@$event["links"]["organizer"][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])){
            	$params["canPostNews"] = true;
				$params["canManageNews"] = true;
            }
            
            $params["event"] = $event; 
        }
        else if ($type=="city"){
	        if (@Yii::app()->session["userId"])
				$params["canPostNews"] = true;
        }
		else if ($type=="pixels"){
			$params["canPostNews"] = true;
		}

		if($type == "citoyens") {
			if (@$viewer && $viewer != null){
				$scope=array(
					array("scope.type"=> "public"),
					array("scope.type"=> "restricted")
				);
				if (@$params["canManageNews"] && $params["canManageNews"])
					array_push($scope,array("scope.type"=>"private"));
				$where = array('$and' => array(
					array('$or' => 
						array(
							array("author"=> $id), 
							array('$and' => 
								array(
									array("target.id"=> $id), 
									array("target.type" => "citoyens")
								) 
							) 
						)
					),
					array('$or' => 
						$scope
					),
					array('created' => array(
							'$lt' => $date
						)
					),
					)	
				);
			}
			else{
				$authorFollowedAndMe=[];
				array_push($authorFollowedAndMe,array("author"=>$id));
				if(@$person["links"]["memberOf"] && !empty($person["links"]["memberOf"])){
					foreach ($person["links"]["memberOf"] as $key => $data){
						array_push($authorFollowedAndMe,array("target.id"=>$key, "target.type" => "organizations"));
					}
				}
				if(@$person["links"]["projects"] && !empty($person["links"]["projects"])){
					foreach ($person["links"]["projects"] as $key => $data){
						array_push($authorFollowedAndMe,array("target.id"=>$key, "target.type" => "projects"));
					}
				}
				if(@$person["links"]["follows"] && !empty($person["links"]["follows"])){
					foreach ($person["links"]["follows"] as $key => $data){
						$followNews=array('$and'=>array(
												array("target.id"=>$key, "target.type" => $data["type"]),
												array('$or'=>
													array(
														array("scope.type" => "public"),
														array("scope.type" => "restricted")
													)
												)
											)
										);
						array_push($authorFollowedAndMe,$followNews);
						//array_push($authorFollowedAndMe,$followActivity);
					}
				}
				if(@$person["address"]["codeInsee"])
					array_push($authorFollowedAndMe, array("scope.cities." => $person["address"]["codeInsee"],"type" => "activityStream"));
		    
		        $where = array(
		        	'$and' => array(
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
		}
		else if($type == "organizations" || $type == "projects" || $type == "events"){
			$scope=array(
					array("scope.type"=> "public"),
					array("scope.type"=> "restricted"),
			);
			if (@$params["canManageNews"] && $params["canManageNews"])
				array_push($scope,array("scope.type"=>"private"));
			else if(@Yii::app()->session["userId"])
				array_push($scope,array("author"=>Yii::app()->session["userId"]));
			$whereScope=array('$or'=>$scope);

			$where = array('$and' => array(
						array('$or'=>array(
							array("target.type"=> $type,"target.id"=> $id),
						)),
					$whereScope,
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
					array("target.type"=> $type),
					array('created' => array(
							'$lt' => $date
						)
					),
	        	)	
			);
		}
		else if($type == "city"){
			$where = array('$and' => array(
					array("scope.cities.codeInsee" => $id, "scope.type" => "public" ),
					array("target.type" => array('$ne' => "pixels")),
					array('created' => array(
							'$lt' => $date
						)
					),
	        	)	
			);
		}
		
		//Exclude => If there is more than 5 reportAbuse
		$where = array_merge($where,  array('$or' => array(
												array("reportAbuseCount" => array('$lt' => 5)),
												array("reportAbuseCount" => array('$exists'=>0))
											))
		);
		$news= News::getNewsForObjectId($where,array("created"=>-1),$type);
		// Sort news order by created 
		$news = News::sortNews($news, array('created'=>SORT_DESC));
        //TODO : reorganise by created date
		$params["news"] = $news; 
		$params["tags"] = Tags::getActiveTags();
		$params["contextParentType"] = $type; 
		$params["contextParentId"] = $id;
		$params["userCP"] = Yii::app()->session['userCP'];
		$params["limitDate"] = end($news);
		if(@$viewer && $viewer != null){
			$params["viewer"]=$viewer;
		}
								//print_r($params["news"]);
								
		if(Yii::app()->request->isAjaxRequest){
			if (@$_GET["isFirst"]){
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