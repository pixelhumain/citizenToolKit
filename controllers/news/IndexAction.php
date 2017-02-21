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
	        	$locality = isset($_POST['locality']) ? trim(urldecode($_POST['locality'])) : null;
				//$searchType = isset($_POST['searchType']) ? $_POST['searchType'] : null;
				$searchBy = isset($_POST['searchBy']) ? $_POST['searchBy'] : "INSEE";
				$tagSearch = isset($_POST['tagSearch']) ? $_POST['tagSearch'] : "";
				$params["locality"] = $locality;
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
			$params["authorizedToStock"]= Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE);
			$params["contextParentType"] = $type; 
			$params["contextParentId"] = $id;
			$params["parent"]=@$parent;
		//} else{
		//	$parent=@$_POST["parent"];
		//}
			//error_log("le type :". $type);
			//Define condition of each wall generated datas
			if($type == "citoyens") {
				//if (!@Yii::app()->session["userId"] || (@Yii::app()->session["userId"] && Yii::app()->session["userId"]!=$id) || (!@$isLive)){
				if(!@$isLive){
					error_log("message 1");
					$scope=array(
						array("scope.type"=> "public"),
						array("scope.type"=> "restricted")
					);
					if (@$params["canManageNews"] && $params["canManageNews"])
						array_push($scope,array("scope.type"=>"private"));
					if(@Yii::app()->session["userId"]){
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
									array("author"=> $id,"targetIsAuthor"=>array('$exists'=>false),"type"=>"news"), 
									array("target.id"=> $id, "target.type" => Person::COLLECTION)  
								)
							),
							array('$or' => 
								$scope
							),
						)	
					);
					//echo '<pre>';var_dump($where);echo '</pre>'; return;
				}
				else{
					error_log("message 2");
					$authorFollowedAndMe=[];
					array_push($authorFollowedAndMe,array("author"=>$id));
					array_push($authorFollowedAndMe,array("target.id"=> $id, 
														"target.type" => Person::COLLECTION));
					array_push($authorFollowedAndMe,array("mentions.id" => $id, 
															"mentions.type" => Person::COLLECTION));

					//echo '<pre>';var_dump($parent);echo '</pre>'; return;
					if(@$parent["links"]["memberOf"] && !empty($parent["links"]["memberOf"])){
						foreach ($parent["links"]["memberOf"] as $key => $data){
							array_push($authorFollowedAndMe,array("target.id"=>$key, "target.type" => Organization::COLLECTION));
							array_push($authorFollowedAndMe,array("mentions.id" => $key, "mentions.type" => Organization::COLLECTION));
						}
					}
					if(@$parent["links"]["projects"] && !empty($parent["links"]["projects"])){
						foreach ($parent["links"]["projects"] as $key => $data){
							array_push($authorFollowedAndMe,array("target.id"=>$key, "target.type" => Project::COLLECTION));
							array_push($authorFollowedAndMe,array("mentions.id" => $key, "mentions.type" => Project::COLLECTION));

						}
					}
					if(@$parent["links"]["events"] && !empty($parent["links"]["events"])){
						foreach ($parent["links"]["events"] as $key => $data){
							array_push($authorFollowedAndMe,array("target.id" => $key, "target.type" => Event::COLLECTION));
							array_push($authorFollowedAndMe,array("mentions.id" => $key, "mentions.type" => Event::COLLECTION));

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
			    	//error_log("message 3");
					
			        $where = array(
			        	'$and' => array(
							array('$or'=> 
									$authorFollowedAndMe
							),
							array("type" => array('$ne' => "pixels")),
			        	)	
			        );
			        //echo '<pre>';var_dump($where);echo '</pre>'; return;
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
								array("mentions.type"=> $type,"mentions.id"=> $id)
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
				/***********************************  DEFINE LOCALITY QUERY   ***************************************/
		  		$localityReferences['CITYKEY'] = "scope.cities.codeInsee";
		  		$localityReferences['CODE_POSTAL'] = "scope.cities.postalCode";
		  		$localityReferences['DEPARTEMENT'] = "scope.cities.postalCode";//Spécifique
		  		$localityReferences['REGION'] = ""; //Spécifique

		  		foreach ($localityReferences as $key => $value) 
		  		{
		  			if(isset($_POST["searchLocality".$key]) 
	  				&& is_array($_POST["searchLocality".$key])
	  				&& count($_POST["searchLocality".$key])>0)
		  			{
		  				foreach ($_POST["searchLocality".$key] as $localityRef) 
		  				{
		  					$locality = utf8_encode($locality);
		  					if(isset($localityRef) && $localityRef != ""){
			  					//error_log("locality :  ".$localityRef. " - " .$key);
			  					//OneRegion
			  					if($key == "CITYKEY"){
					        		//value.country + "_" + value.insee + "-" + value.postalCodes[0].postalCode; 
					        		//error_log("CITYKEY " .$localityRef );
					        		$city = City::getByUnikey($localityRef);
					        		$queryLocality = array(
					        				"scope.cities.codeInsee" => $city["insee"]
					        				//"scope.cities.codeInsee" => new MongoRegex("/".$city["insee"]."/i"),
					        				//"scope.cities.postalCode" => new MongoRegex("/".$city["cp"]."/i"),
					        		);
					        		if (isset($city["cp"])) {
					        			$queryLocality["scope.cities.postalCode"] = $city["cp"];
					        		}
				  				}
				  				elseif($key == "CODE_POSTAL") { //error_log($localityRef);
				  					$cities = PHDB::find( City::COLLECTION, array("postalCodes.postalCode" => $localityRef), array("insee"));
				  					$inQuestInsee = array();
				  					foreach($cities as $key => $val){ $inQuestInsee[] = $val["insee"]; error_log($val["insee"]); }
					        		$queryLocality = array('$or' => array( array($value => new MongoRegex("/^".$localityRef."/i")),
					        												array("scope.cities.codeInsee" => array('$in' => $inQuestInsee)) )
					        							 );
				  				}
				  				elseif($key == "DEPARTEMENT") { error_log("DEPARTEMENT : " . $localityRef);
				        			$dep = PHDB::findOne( City::COLLECTION, array("depName" => $localityRef), array("dep"));	
				        			if(isset($dep["dep"])){
					        			//$queryLocality = array($value => new MongoRegex("/^".$dep["dep"]."/i"));
					        			$queryLocality = array('$or' => array(
									        						array($value => new MongoRegex("/^".$dep["dep"]."/i")),
									        						array("scope.cities.codeInsee" => new MongoRegex("/^".$dep["dep"]."/i")),
									        						array("scope.departements.name" => $localityRef)
									        						));
				        			}
								}
					        	elseif($key == "REGION") {
				        			$deps = PHDB::find( City::COLLECTION, array("regionName" => $localityRef), array("dep", "depName"));
				        			$departements = array();
				        			$departementsName = array();
				        			$inQuestCp = array();
				        			$inQuestName = array();
				        			if(is_array($deps))
				        				foreach($deps as $index => $value)
					        			{
					        				if(!in_array($value["dep"], $departements))
					        				{   //error_log("depppppp :".@$value["depName"]);
						        				$departements[] = $value["dep"];
						        				if(@$value["dep"])
						        				$inQuestCp[] = new MongoRegex("/^".$value["dep"]."/i");
						        				if(@$value["depName"] && !in_array($value["depName"], $departementsName))
									        	$inQuestName[] = $value["depName"];
									        	
									        	$departementsName[] = @$value["depName"];
						        				
									        }
					        			}
					        			$queryLocality = array('$or' => array(								        							
					        								array("scope.cities.postalCode" => array('$in' => $inQuestCp)),
									        				array("scope.cities.codeInsee" => array('$in' => $inQuestCp)),
									        				array("scope.departements.name" => array('$in' => $inQuestName)),
									        				array("scope.regions.name" => $localityRef)
									        						));
				        		} //error_log("HEEEEEEEEEEEEEEEEEEEee");
			  					//Consolidate Queries
			  					if(isset($allQueryLocality) && isset($queryLocality)){
			  						$allQueryLocality = array('$or' => array( $allQueryLocality ,$queryLocality));
			  					}else if(isset($queryLocality)){
			  						$allQueryLocality = $queryLocality;
			  					}
			  					unset($queryLocality);
			  				}
		  				}
		  			}
		  		}
		  		$where = array( "scope.type" => "public",
		  						"target.type" => array('$ne' => "pixels"),
		  						);

		  		if(@$_POST["typeNews"]) $where["type"] = $_POST["typeNews"];

		  		
					//error_log("typeNews : ".@$_POST["typeNews"]);			
				if(@$allQueryLocality){
					$where = array_merge($where, $allQueryLocality);
				}
				//echo '<pre>';var_dump($where);echo '</pre>'; return;
		  		
		  	}
			if(@$_POST["tagSearch"] && !empty($_POST["tagSearch"])){
					$queryTag = array();
					foreach ($_POST["tagSearch"] as $key => $tag) {
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
					if($data == "news" || $data == "idea" || $data == "question" || $data == "announce" || $data == "information")
						$searchType[]=array("type" => $data);
					else
						$searchType[]=array("object.objectType" => $data);
				}
				
				//
				if(isset($where['$and']) && isset($searchType)){
					$where['$and'][] = array('$or' =>$searchType);
				}else if(isset($searchType)){
					$where = array_merge($where, array('$and' => array(array('$or' =>$searchType))));
				}
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
			// $where = array_merge($where,  array('$or' => array(
			// 											array("reportAbuseCount" => array('$lt' => 5)),
			// 											array("reportAbuseCount" => array('$exists'=>0))
			// 										)));
			
			//Exclude => If isAnAbuse
			$where = array_merge($where,  array( 'isAnAbuse' => array('$ne' => true) ) );
			
			$where = array_merge($where,  array('created' => array( '$lt' => $date ) ) );

			if(@$_POST["textSearch"] && $_POST["textSearch"]!="")
			$where = array_merge($where,  array('text' => new MongoRegex("/".$_POST["textSearch"]."/i") ) );

			//echo '<pre>';var_dump($_POST);echo '</pre>';
			//echo '<pre>';var_dump($where);echo '</pre>'; return;
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

		if(sizeOf(@$_POST['searchType'])==1){
			$params["filterTypeNews"]=$_POST['searchType'][0];
		}

		$params["firstView"] = "news";

		$nbCol = @$_GET["nbCol"] ? $_GET["nbCol"] : 1;
		if(@$_GET["tpl"]=="co2") $params["nbCol"] = $nbCol;

		//$params["params"] = $params;
		//$params["params"]["news"] = "";
		error_log(@$params["nbCol"]);
		error_log(Yii::app()->request->isAjaxRequest ? "isAjax".@$_GET["tpl"] : "notAjax".@$_GET["tpl"]);			
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