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
					//if (Yii::app()->session["userId"]==$id){
					//	$params["canManageNews"]=false;
					//}
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
	        }
			else if ($type=="pixels"){
				if (@Yii::app()->session["userId"])
					$params["canPostNews"] = true;
			}
			$params["authorizedToStock"]= Document::authorizedToStock($id, $type,Document::DOC_TYPE_IMAGE);
			$params["contextParentType"] = $type; 
			$params["contextParentId"] = $id;
			$params["parent"]=@$parent;
		} else{
			$parent=@$_POST["parent"];
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
					array_push($authorFollowedAndMe,array("mentions.id" => $id, 
															"mentions.type" => Person::COLLECTION));
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
		  		$localityReferences['NAME'] = "scope.cities.codeInsee";
		  		$localityReferences['CODE_POSTAL_INSEE'] = "scope.cities.postalCode";
		  		$localityReferences['DEPARTEMENT'] = "scope.cities.postalCode";//Spécifique
		  		$localityReferences['REGION'] = ""; //Spécifique
		  		$localityReferences['INSEE'] = "scope.cities.codeInsee";

		  		foreach ($localityReferences as $key => $value) 
		  		{
		  			if(isset($_POST["searchLocality".$key]) 
		  				&& is_array($_POST["searchLocality".$key])
		  				&& count($_POST["searchLocality".$key])>0)
		  			{
		  				foreach ($_POST["searchLocality".$key] as $localityRef) 
		  				{
		  					if(isset($localityRef) && $localityRef != ""){
			  					error_log("locality :  ".$localityRef. " - " .$key);
			  					//OneRegion
			  					if($key == "REGION") 
			  					{ 
				        			$deps = PHDB::find( City::COLLECTION, array("regionName" => $localityRef), array("dep"));
				        			$departements = array();
				        			$inQuest = array();
				        			if(is_array($deps))foreach($deps as $index => $value)
				        			{
				        				if(!in_array($value["dep"], $departements))
				        				{
					        				$departements[] = $value["dep"];
					        				$inQuest[] = new MongoRegex("/^".$value["dep"]."/i");
								        	$queryLocality = array("scope.cities.postalCode" => array('$in' => $inQuest));
								        }
				        			}
				        		}elseif($key == "NAME"){
					        		//value.country + "_" + value.insee + "-" + value.postalCodes[0].postalCode; 
					        		error_log("NAME " .$localityRef );
					        		$city = City::getByUnikey($localityRef);
					        		$queryLocality = array(
					        				//"address.addressCountry" => new MongoRegex("/".$city["country"]."/i"),
					        				"scope.cities.codeInsee" => new MongoRegex("/".$city["insee"]."/i"),
					        				"scope.cities.postalCode" => new MongoRegex("/".$city["cp"]."/i"),
					        		);
				  				}elseif($key == "DEPARTEMENT") {
				        			$dep = PHDB::findOne( City::COLLECTION, array("depName" => $localityRef), array("dep"));	
				        			$queryLocality = array($value => new MongoRegex("/^".$dep["dep"]."/i"));
								}//OneLocality
					        	else{
					        		$queryLocality = array($value => new MongoRegex("/^".$localityRef."/i"));
				  				}

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
		  		// if(isset($allQueryLocality) && is_array($allQueryLocality))
		  		// 	$where = array('$and' => array($where, $allQueryLocality));

		  		//$where = array("scope.type" => "public","target.type" => array('$ne' => "pixels"), $allQueryLocality);
				//if(@$allQueryLocality){
					//$where = array_merge($where,$allQueryLocality);
		  			//$where = array('$and' => array($where, ));
				//}
		  		/***********************************  DEFINE LOCALITY QUERY   ***************************************/
		  		//echo '<pre>';var_dump($where);echo '</pre>';
		        /*
				/*if(!empty($_POST['locality'])){
					$locality = isset($_POST['locality']) ? trim(urldecode($_POST['locality'])) : null;
					//$searchType = isset($_POST['searchType']) ? $_POST['searchType'] : null;
					$searchBy = isset($_POST['searchBy']) ? $_POST['searchBy'] : "INSEE";
					
			        if($searchBy == "CODE_POSTAL_INSEE") {
			        	$queryLocality = array("scope.cities.postalCode" => $locality );
		        	}
		        	if($searchBy == "DEPARTEMENT") {
		        		$queryLocality = array("scope.cities.postalCode" 
								=> new MongoRegex("/^".$locality."/i"));
		        	}
		        	if($searchBy == "REGION") {
		        		//#TODO GET REGION NAME | CITIES.DEP = myDep
		        		$regionName = PHDB::findOne( City::COLLECTION, array("insee" => $locality), array("regionName", "dep"));
		        		
						if(isset($regionName["regionName"])){ //quand la city a bien la donnée "regionName"
		        			$regionName = $regionName["regionName"];
		        			//#TODO GET ALL DEPARTMENT BY REGION
		        			$deps = PHDB::find( City::COLLECTION, array("regionName" => $regionName), array("dep"));
		        			$departements = array();
		        			$inQuest = array();
		        			foreach($deps as $index => $value){
		        				if(!in_array($value["dep"], $departements)){
			        				$departements[] = $value["dep"];
			        				$inQuest[] = new MongoRegex("/^".$value["dep"]."/i");
						        	$queryLocality = array("scope.cities.postalCode" => array('$in' => $inQuest));
			        				
						        }
		        			}	
		        		}else{ //quand la city communectée n'a pas la donnée "regionName", on prend son département à la place
		        			$regionName = isset($regionName["dep"]) ? $regionName["dep"] : "";
		        			$queryLocality = array("scope.cities.postalCode" 
								=> new MongoRegex("/^".$regionName."/i"));
		        		}
		        		error_log("regionName : ".$regionName );
		        	}
		        	if($searchBy == "INSEE") {
		        		$queryLocality = array("scope.cities.codeInsee" => $locality );
		        	}
	        	}*/
				
			}
			if(@$_POST["tagSearch"] && !empty($_POST["tagSearch"])){
					$querySearch = array( "tags" => array('$in' => array(new MongoRegex("/".$_POST["tagSearch"]."/i")))) ;
					$where = array_merge($where,$querySearch); 			
			}
			if(@$_POST['searchType']){
				$searchType=array();
				foreach($_POST['searchType'] as $data){
					if($data == "news" || $data == "idea" || $data == "question" || $data == "announce" || $data == "information")
						$searchType[]=array("type" => $data);
					else
						$searchType[]=array("object.objectType" => $data);
				}
				$where = array_merge($where, array('$and' => array(array('$or' =>$searchType))));
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

			//echo '<pre>';var_dump($_POST);echo '</pre>';
			//echo '<pre>';var_dump($where);echo '</pre>';
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