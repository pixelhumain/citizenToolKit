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
        if(@$date && $date != null){
			$date = $date;
		}
		else{
			$date=time();
		}
		$date=new MongoDate($date);
		$news=array();
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
		// Actions done at first loading of the page
		// perform time of wall loading
		if (@$_GET["isFirst"]){
			
			if(!@$type || empty($type))
				$type = Person::COLLECTION;
			$params["type"] = $type; 
			// Define parent and authorizations to manage and post news on wall's entity
	        if( $type == Project::COLLECTION ) {
	            $parent = Project::getById($id);
	            if(@Yii::app()->session["userId"]){
	            	$params["canPostNews"] = true;
	                if(@$parent["links"]["contributors"][Yii::app()->session["userId"]] && !@$parent["links"]["contributors"][Yii::app()->session["userId"]][TO_BE_VALIDATED])
	            	$params["canManageNews"] = true;
	            }
	        } 
	        else if( $type == Person::COLLECTION ) {
	            $parent = Person::getById($id);
	            if (@Yii::app()->session["userId"]){
					$params["canPostNews"] = true;
					if (Yii::app()->session["userId"]==$id){
						$params["canManageNews"]=true;
					}
				}

	        } 
	        else if( $type == Organization::COLLECTION) {
	            $parent = Organization::getById($id);
	            if(@Yii::app()->session["userId"]){
					$params["canPostNews"] = true;
	            	if (@$parent["links"]["members"][Yii::app()->session["userId"]] && !@$parent["links"]["members"][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])
	            		$params["canManageNews"] = true;
				}	

	        }
	        else if( $type == Event::COLLECTION ) {
	            $parent = Event::getById($id);
	            if((@Yii::app()->session["userId"] && @$parent["links"]["attendees"][Yii::app()->session["userId"]]) ||
	            	(@Yii::app()->session["userId"] && @$parent["links"]["organizer"][Yii::app()->session["userId"]] && !@$parent["links"]["organizer"][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])){
	            	$params["canPostNews"] = true;
	            	if(@$parent["links"]["attendees"][Yii::app()->session["userId"]]["isAdmin"])
						$params["canManageNews"] = true;
	            }
	        }
	        else if ($type=="city"){
		        if (@Yii::app()->session["userId"])
					$params["canPostNews"] = true;
	        }
			else if ($type=="pixels"){
				if (@Yii::app()->session["userId"])
					$params["canPostNews"] = true;
			}
			$params["authorizedToStock"]= Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE);
			$params["contextParentType"] = $type; 
			$params["contextParentId"] = $id;
			$params["parent"]=@$parent;
		}
			//Define condition of each wall generated datas
			if($type == "citoyens") {
				if (!@Yii::app()->session["userId"] || (@Yii::app()->session["userId"] && Yii::app()->session["userId"]!=$id) || (@$viewer && $viewer != null)){

					$scope=array(
						array("scope.type"=> "public"),
						array("scope.type"=> "restricted")
					);
					if (@$params["canManageNews"] && $params["canManageNews"])
						array_push($scope,array("scope.type"=>"private"));
					if(@$viewer || @Yii::app()->session["userId"]){
						array_push($scope,
									array("author"=> Yii::app()->session["userId"],
											"target.id"=> $id,
											"target.type" => Person::COLLECTION)
								);
					}
					$where = array('$and' => 
						array(
							array('$or' => 
								array(
									array("author"=> $id), 
									array('$and' => 
										array(
											array("target.id"=> $id), 
											array("target.type" => Person::COLLECTION)
										) 
									) 
								)
							),
							array('$or' => 
								$scope
							),
						)	
					);
				}
				else{
					$authorFollowedAndMe=[];
					array_push($authorFollowedAndMe,array("author"=>$id));
					array_push($authorFollowedAndMe,array("target.id"=> $id, 
														"target.type" => Person::COLLECTION));
					if(@$parent["links"]["memberOf"] && !empty($parent["links"]["memberOf"])){
						foreach ($parent["links"]["memberOf"] as $key => $data){
							array_push($authorFollowedAndMe,array("target.id"=>$key, "target.type" => "organizations"));
						}
					}
					if(@$parent["links"]["projects"] && !empty($parent["links"]["projects"])){
						foreach ($parent["links"]["projects"] as $key => $data){
							array_push($authorFollowedAndMe,array("target.id"=>$key, "target.type" => "projects"));
						}
					}
					if(@$parent["links"]["events"] && !empty($parent["links"]["events"])){
						foreach ($parent["links"]["events"] as $key => $data){
							array_push($authorFollowedAndMe,array("target.id"=>$key, "target.type" => "events"));
						}
					}
					if(@$parent["links"]["follows"] && !empty($parent["links"]["follows"])){
						foreach ($parent["links"]["follows"] as $key => $data){
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
						}
					}
					if(@$parent["address"]["codeInsee"])
						array_push($authorFollowedAndMe, array("scope.cities." => $parent["address"]["codeInsee"],"type" => "activityStream"));
			    
			        $where = array(
			        	'$and' => array(
							array('$or'=> 
									$authorFollowedAndMe
							),
							array("type" => array('$ne' => "pixels")),
			        	)	
			        );
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
		        	)	
				);
			}
			else if ($type == "pixels"){
				$where = array('$and' => array(
						array("text" => array('$exists'=>1)),
						array("target.type"=> $type),
		        	)	
				);
			}
			else if($type == "city"){
				$where = array('$and' => array(
						array("scope.cities.codeInsee" => $id, "scope.type" => "public" ),
						array("target.type" => array('$ne' => "pixels")),
		        	)	
				);
			}
			
			//Exclude => If there is more than 5 reportAbuse
			$where = array_merge($where,  array('$or' => array(
													array("reportAbuseCount" => array('$lt' => 5)),
													array("reportAbuseCount" => array('$exists'=>0))
												))
			);
	
			//Exclude => If isAnAbuse
			$where = array_merge($where,  array(
													'isAnAbuse' => array('$ne' => true)
												)
			);
			$where = array_merge($where,  array('created' => array(
													'$lt' => $date
													)
												)
			);

			
		/*}
		else{
			$where=$_POST["condition"];
			$where["created"]=array('$lt' => $date);
		}*/
		//print_r($where);
		if(!empty($where))
			$news= News::getNewsForObjectId($where,array("created"=>-1),$type);
		
		
		// Sort news order by created 
		$news = News::sortNews($news, array('created'=>SORT_DESC));
        //TODO : reorganise by created date
		$params["news"] = $news;
		$params["tags"] = Tags::getActiveTags();
		$params["authorizedToStock"]= Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE);			$params["tags"] = Tags::getActiveTags();
		$params["contextParentType"] = $type; 
		$params["contextParentId"] = $id;
		$params["userCP"] = Yii::app()->session['userCP'];
		$params["limitDate"] = end($news);
		if(@$viewer && $viewer != null){
			$params["viewer"]=$viewer;
		}
							
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