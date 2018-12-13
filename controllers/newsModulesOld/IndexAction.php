<?php
class IndexAction extends CAction
{
    public function run($type=null, $id= null, $date = null, $isLive = null,$streamType="news", $textSearch=null)
    {
    	$controller=$this->getController();
        $controller->title = "Timeline";
        $controller->subTitle = "NEWS comes from everywhere, and from anyone.";
        $controller->pageTitle = "Communecter - Timeline Globale";
        $news = array();
        if(@$date && $date != null && $date != ""){
			$date = $date;
		}
		else{
			$date=time();
		}

		//bloque l'acces a la page 'my network'
		//si le views n'est pas renseigné
		if($type == "citoyens" 
		&& Yii::app()->session["userId"] != $id 
		&& (!@$viewer || $viewer == null || $viewer == "")){
			$viewer = Yii::app()->session["userId"];
			//throw new CTKException("Impossible to access this stream");
		}


		$date=new MongoDate($date);
		$news=array();
		$params = array();
		$where = array();
		if (!isset($id)){
			if($type!="pixels"){
				if($type=="city"){
					$id="";//$_GET["insee"];
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
	//	if (@$_GET["isFirst"]){
			
			if(!@$type || empty($type))
				$type = Person::COLLECTION;
			$params["type"] = $type; 
			// Define parent and authorizations to manage and post news on wall's entity
	        if( $type == Project::COLLECTION ) {
	            $parent = Project::getById($id);
	            if(@Yii::app()->session["userId"]){
	            	$params["canPostNews"] = true;
	                if(@$parent["links"]["contributors"][Yii::app()->session["userId"]] && !@$parent["links"]["contributors"][Yii::app()->session["userId"]][Link::TO_BE_VALIDATED])
	            	$params["canManageNews"] = true;
	            }
	        } 
	        else if( $type == Person::COLLECTION ) {
	            $parent = Person::getById($id);
	            if (@Yii::app()->session["userId"]){
					$params["canPostNews"] = true;
					if (Yii::app()->session["userId"]==$id && $isLive!=true){
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
	        else if( $type == Place::COLLECTION) { error_log("PLACE");
	            $parent = Place::getById($id);
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
	        	$localities = isset($_POST['locality']) ? $_POST['locality'] : null;
	        	//$searchType = isset($_POST['searchType']) ? $_POST['searchType'] : null;
				$searchBy = isset($_POST['searchBy']) ? $_POST['searchBy'] : "INSEE";
				$tagSearch = isset($_POST['tagSearch']) ? $_POST['tagSearch'] : "";
				$params["localities"] = $localities;
				$params["searchBy"] = $searchBy;
				$params["tagSearch"] = $tagSearch;
		        if (@Yii::app()->session["userId"])
					$params["canPostNews"] = true;
				$isLive=true;
	        }
			else if ($type=="pixels"){
				if (@Yii::app()->session["userId"])
					$params["canPostNews"] = true;
			}
			//$params["authorizedToStock"]= Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE);
			$params["contextParentType"] = $type; 
			$params["contextParentId"] = $id;
			$params["parent"]=@$parent;
		//} else{
		//	$parent=@$_POST["parent"];
		//}
			//error_log("le type :". $type);
			//Define condition of each wall generated datas
			if($type == Person::COLLECTION) {
				//if (!@Yii::app()->session["userId"] || (@Yii::app()->session["userId"] && Yii::app()->session["userId"]!=$id) || (!@$isLive)){
				if(@$isLive && (@Yii::app()->session["userId"] && $id == Yii::app()->session["userId"])){
					//error_log("message 2");
					$authorFollowedAndMe=[];
					/*$or:[
					{'author':this.userId},
					{'target.id': {$in:arrayIds}},
					{'mentions.id': {$in:arrayIds}},
					{'target.id': {$in:followsArrayIds},'scope.type':{$in:['public','restricted']}},*/ 
					/*array_push($authorFollowedAndMe,array("sharedBy"=>array('$in'=>array($id))));
					array_push($authorFollowedAndMe,array("author"=>$id));
					array_push($authorFollowedAndMe,array("target.id"=> $id, 
														"target.type" => Person::COLLECTION));
					array_push($authorFollowedAndMe,array("mentions.id" => $id, 
															"mentions.type" => Person::COLLECTION));*/

					//echo '<pre>';var_dump($parent);echo '</pre>'; return;
					$arrayIds=[$id];
					$followsArrayIds=[];
					if(@$parent["links"]["memberOf"] && !empty($parent["links"]["memberOf"])){
						foreach ($parent["links"]["memberOf"] as $key => $data){
							if(!@$data[Link::TO_BE_VALIDATED])
								array_push($arrayIds,$key);
							//array_push($authorFollowedAndMe,array("target.id"=>$key, "target.type" => Organization::COLLECTION));
							//array_push($authorFollowedAndMe,array("mentions.id" => $key, "mentions.type" => Organization::COLLECTION));
						}
					}
					if(@$parent["links"]["projects"] && !empty($parent["links"]["projects"])){
						foreach ($parent["links"]["projects"] as $key => $data){
							if(!@$data[Link::TO_BE_VALIDATED])
								array_push($arrayIds,$key);
							/*array_push($authorFollowedAndMe,array("target.id"=>$key, "target.type" => Project::COLLECTION));
							array_push($authorFollowedAndMe,array("mentions.id" => $key, "mentions.type" => Project::COLLECTION));*/

						}
					}
					if(@$parent["links"]["events"] && !empty($parent["links"]["events"])){
						foreach ($parent["links"]["events"] as $key => $data){
							if(!@$data[Link::TO_BE_VALIDATED])
								array_push($arrayIds,$key);
							/*array_push($authorFollowedAndMe,array("target.id" => $key, "target.type" => Event::COLLECTION));
							array_push($authorFollowedAndMe,array("mentions.id" => $key, "mentions.type" => Event::COLLECTION));*/

						}
					}
					if(@$parent["links"]["follows"] && !empty($parent["links"]["follows"])){
						foreach ($parent["links"]["follows"] as $key => $data){
							array_push($followsArrayIds,$key);
							/*$followNews=array('$and'=>array(
													array("target.id"=>$key, "target.type" => $data["type"]),
													array('$or'=>
														array(
															array("scope.type" => "public"),
															array("scope.type" => "restricted")
														)
													)
												)
											);
							array_push($authorFollowedAndMe,$followNews);*/
						}
					}
										//var_dump($followsArrayIds);
			        $where = array(
			        	'$and' => array(
							array('$or'=> 
								array(
									array("author"=>$id), 
									array("sharedBy.id"=>array('$in'=>array($id))), 
									array("sharedBy.id"=>array('$in'=>array($arrayIds))), 
									array("target.id" =>  array('$in' => $arrayIds)),
									array("mentions.id" => array('$in' => $arrayIds)),
									array(
										"target.id"=> array('$in' => $followsArrayIds),
										"sharedBy.id"=> array('$in' => $followsArrayIds),
										//"sharedBy.id"=>array('$in'=>array($id)), 
										"scope.type" => array('$in'=> ['public','restricted'])
									)
								)
							),
							array("type" => array('$ne' => "pixels")),
			        	)	
			        );
				}
				else{
					//error_log("message 1");
					$scope=["public","restricted"];
					if (@$params["canManageNews"] && $params["canManageNews"]){
						$orRequest=array(
							array("author"=> $id,"targetIsAuthor"=>array('$exists'=>false),"type"=>"news"), 
							array("target.id"=> $id, "target.type" => Person::COLLECTION),
							array("sharedBy.id"=>array('$in'=>array($id)), "verb"=> "share"),
						);
					} else {
						$orRequest=array(
							array("author"=> $id,
								"targetIsAuthor"=>array('$exists'=>false),
								//"type"=>"news", 
								"scope.type"=> array('$in'=> $scope)
							), 
							array("target.id"=> $id, "scope.type"=> array('$in'=> $scope),
							array("sharedBy.id"=>array('$in'=>array($id)),"verb"=> "share"))
						);
					}
					if((!@$params["canManageNews"] || $params["canManageNews"] == false ) && @Yii::app()->session["userId"]){
						array_push($orRequest,
									array("author"=> Yii::app()->session["userId"],
											"target.id"=> $id)
								);
					}
					$where = array('$or' => $orRequest);
				}
			}
			else if(in_array($type, [Organization::COLLECTION, Project::COLLECTION, Event::COLLECTION, Place::COLLECTION])){
				$scope=["public","restricted"];
				$arrayIds=[];
				if(@$parent["links"]["projects"] && !empty($parent["links"]["projects"])){
					foreach ($parent["links"]["projects"] as $key => $data){
						if(!@$data[Link::TO_BE_VALIDATED])
							array_push($arrayIds,$key);
					}
				}
				if(@$parent["links"]["events"] && !empty($parent["links"]["events"])){
					foreach ($parent["links"]["events"] as $key => $data){
						if(!@$data[Link::TO_BE_VALIDATED])
							array_push($arrayIds,$key);
					}
				}
				if (@$params["canManageNews"] && $params["canManageNews"]){
					$orRequest=array(
						array("mentions.id"=>$id,"scope.type"=>array('$in'=>$scope)),
						array("target.id"=>$id)
					);
				}else{
					$orRequest=array(
						array("mentions.id"=>$id,"scope.type"=>array('$in'=>$scope)),
						array("target.id"=>$id, 
								'$or'=> array(
								array("scope.type"=>array('$in'=>$scope)),
								array("author"=>Yii::app()->session["userId"])
							)
						)
					);
				}
				array_push($orRequest,
					array('$or'=>array(
							array("sharedBy.id"=>array('$in'=>array($arrayIds))), 
							array("target.id" =>  array('$in' => $arrayIds))),
						"scope.type"=>array('$in'=>$scope)));
				$where = array('$or'=>$orRequest);
			}
			else if ($type == "pixels"){
				$where = array('$and' => array(
						array("text" => array('$exists'=>1)),
						array("target.type"=> $type),
		        	)	
				);
			}
			else if($type == "city"){
				/***********************************  DEFINE LOCALITY QUERY   ***************************************/
		  		
		  		$allQueryLocality = array();
		  		if(!empty($localities)){
		  			foreach ($localities as $key => $locality){
						if(!empty($locality)){

							if($locality["type"] == City::COLLECTION){
								$queryLocality = array("scope.localities.parentId" => $locality["id"], "scope.localities.parentType" =>  $locality["type"]);
								if(!empty($locality["postalCode"]))
									$queryLocality = array_merge($queryLocality, array("scope.localities.postalCode" => new MongoRegex("/^".$locality["postalCode"]."/i")));
							}
							else if($locality["type"] == "cp")
								$queryLocality = array("scope.localities.postalCode" => new MongoRegex("/^".$locality["name"]."/i"));
							else
								$queryLocality = array('$or'=>array(
									array("scope.localities.parentId" => $locality["id"]),
									array("scope.localities.".$locality["type"] => $locality["id"]))
							);
						
							if(empty($allQueryLocality))
								$allQueryLocality = $queryLocality;
							else if(!empty($queryLocality))
								$allQueryLocality = array('$or' => array($allQueryLocality ,$queryLocality));
						}
					}
		  		}
				
				$where = array( "scope.type" => "public",
		  						"target.type" => array('$ne' => "pixels"),
		  						);

				//var_dump($where);

		  		if(@$_POST["typeNews"]) $where["type"] = $_POST["typeNews"];

		  		
					//error_log("typeNews : ".@$_POST["typeNews"]);			
				if(@$allQueryLocality){
					$where = array_merge($where, $allQueryLocality);
					//$where = array('$and' => array( $where , $allQueryLocality ) );
				}

				//echo '<pre>';var_dump($where);echo '</pre>'; return;
		  		
		  	}

		 			
			$queryTag = array();
			if(@$_POST["searchTags"] && !empty($_POST["searchTags"])){
				foreach ($_POST["searchTags"] as $key => $tag) {
					if($tag != "")
					$queryTag[] = new MongoRegex("/".$tag."/i");
				}
				//$querySearch = array( "tags" => array('$in' => $queryTag)) ;

				if(!empty($queryTag))
				$where["tags"] = array('$in' => $queryTag); 			
			}

			
			if(@$_POST['searchType']){
				$searchType=array();
				foreach($_POST['searchType'] as $data){
					if($data == "news")
						$searchType=array("type" => $data);
					else if ($data == "activityStream")
						$searchType=array("type" => $data);
					else if($data == "surveys")
						$searchType=array("object.type"=>"proposals", "verb"=>"publish");
				}
				
				//
				//if(isset($where['$and']) && isset($searchType)){
				//	$where['$and'][] = array('$or' =>$searchType);
				//}else if(isset($searchType)){
				if(!empty($searchType))
					$where = array_merge($where, $searchType);
					//$where = array('$and' => array( $where , array('$and' => array(array('$or' =>$searchType))) ) );
				//}
				//echo '<pre>';var_dump($where);echo '</pre>'; return;
			}
			
				// if(@$_POST['searchType']){
				// 	$searchType=array();
				// 	foreach($_POST['searchType'] as $data){
				// 		if($data == "activityStream")
				// 			$searchType[]=array("object.objectType" => $data);
				// 		else if(@$_POST["typeNews"])
				// 			$searchType[]=array("type" => $_POST["typeNews"]);
				// 		else 
				// 	}
				// 	if(!empty($searchType))
				// 	$where = array_merge($where, array('$and' => array(array('$or' =>$searchType))));
				// }

			//Exclude => If there is more than 5 reportAbuse
			/*$where['$and'][] =  array('$or'=>array(array("reportAbuseCount" => array('$lt' => 5)),
													array("reportAbuseCount" => array('$exists'=>0))
												  ));*/
			//var_dump($where);// exit;
			//Exclude => If isAnAbuse
			$where = array_merge($where,  array( 'isAnAbuse' => array('$ne' => true) ) );
			$where = array_merge($where,  array('sharedBy.updated' => array( '$lt' => $date ) ) );
			$where = array_merge($where, array("target.type" => array('$ne' => "pixels")));

			// $where = array('$and' => array( $where , array( 'isAnAbuse' => array('$ne' => true) ) ) );
			// $where = array('$and' => array( $where , array('sharedBy.updated' => array( '$lt' => $date ) ) ) );
			// $where = array('$and' => array( $where , array("target.type" => array('$ne' => "pixels") ) ) );

			if(@$_POST["name"] && $_POST["name"]!=""){
			
				$textTag=null;
		  		$textSearch = $_POST["name"];
		  		$textTag = explode(" ", $textSearch);
				$hashTag = substr($textSearch, 0, 1);
		  		if(sizeof($textTag)==1 && $hashTag=="#"){
		  			//var_dump($textTag[0]); echo substr($textTag[0], 1, strlen($textTag[0])-1); exit;
		  			$tagClear = substr($textTag[0], 1, strlen($textTag[0])-1);
		  			$textTag = array($textTag[0], $tagClear);
		  			$where["tags"] = array('$in' => $textTag); 		
		  			//var_dump($where["tags"]);	
		  		}else{
		  			//$where = array('$and' => array( $where ,  array('text' => new MongoRegex("/".$_POST["textSearch"]."/i") ) ) );
					$where = array_merge($where,  array('text' => new MongoRegex("/".$_POST["name"]."/i") ) );
		  		}
		  	
				
			}
			//var_dump($where);
			//echo '<pre>';var_dump($_POST);echo '</pre>';
			//echo '<pre>';var_dump($where);echo '</pre>'; return;
		/*}
		else{
			$where=$_POST["condition"];
			$where["created"]=array('$lt' => $date);
		}*/

		//var_dump($where); ///**/exit;
		if(!empty($where))
			$news= News::getNewsForObjectId($where,array("sharedBy.updated"=>-1),$type, @$followsArrayIds);
		//echo count($news);
		// Sort news order by created 
		$news = News::sortNews($news, array('updated'=>SORT_DESC));

		//remove activityStream if user connected can't access his parentRoom (because of room role access)
		$news = Cooperation::checkRoleAccessInNews($news);

		//var_dump($news); exit;
        //TODO : reorganise by created date
		$params["news"] = $news;
		$params["tags"] = Tags::getActiveTags();
		$params["authorizedToStock"]= Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE);			
		$params["tags"] = Tags::getActiveTags();
		$params["contextParentType"] = $type; 
		$params["contextParentId"] = $id;
		$params["userCP"] = Yii::app()->session['userCP'];
		$params["limitDate"] = end($news);

		if(@$parent){
			$params["edit"] = Authorisation::canEditItem(Yii::app()->session["userId"], $type, $parent["_id"]);
			$params["openEdition"] = Authorisation::isOpenEdition($parent["_id"], $type, @$parent["preferences"]);
		}else{
			$params["edit"] = false;
			$params["openEdition"] = false;
		}
		

		if(@$isLive && $isLive != null){
			$params["isLive"]=$isLive;
		} else {
			$params["isLive"]=false;
		}

		if(is_array(@$_POST['searchType']) && count(@$_POST['searchType']) ==1){
			$params["filterTypeNews"]=$_POST['searchType'][0];
		}

		$params["firstView"] = "news";

		$nbCol = @$_GET["nbCol"] ? $_GET["nbCol"] : 1;
		if(@$_GET["tpl"]=="co2") $params["nbCol"] = $nbCol;

		//$params["params"] = $params;
		//$params["params"]["news"] = "";
		error_log(@$params["nbCol"]);
		error_log(Yii::app()->request->isAjaxRequest ? "isAjax".@$_GET["tpl"] : "notAjax".@$_GET["tpl"]);			
		//manage delete in progress status
		if ($type == Organization::COLLECTION || $type == Project::COLLECTION || $type == Event::COLLECTION || $type == Person::COLLECTION)
			$params["deletePending"] = Element::isElementStatusDeletePending($type, $id);

		if(Yii::app()->request->isAjaxRequest){
			if (@$_GET["isFirst"]){
				 if(@$_GET["tpl"]=="co2")
				 echo $controller->renderPartial("indexCO2", $params,true);
				 else
				 echo $controller->renderPartial("index", $params,true);
	       } else{
				//
	       		if(@$_GET["tpl"]=="co2")
				 echo $controller->renderPartial("newsPartialCO2", $params,true);
				else
				echo json_encode($params);
	      }
	    }
	    else
			$controller->render( "index" , $params  );
    }
}