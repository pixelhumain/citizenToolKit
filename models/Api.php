<?php 
class Api {

	const COLLECTION = "gantts";
	
	public static function getData($bindMap, $format = null, $type, $id = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null){
    	$data = null;
		$link = false ;
        $typeResult = "entities";

        if($type == Person::COLLECTION && @$id && $id == Yii::app()->session["userId"]){
          $params["_id"] = new MongoId($id);
          $index = 0 ;
          $limit = 1 ;
          $link = true ;
          $typeResult = "identity";

        }else{
        	$params = array();
			if( @$id ) 
            	$params["_id"] =  new MongoId($id);
          	if($type == City::COLLECTION && @$_GET["insee"])
            	$params["insee"] = $_GET["insee"];

            if( @$tags ){
            	$tagsArray = explode(",", $tags);
				$params["tags"] =  (($multiTags == true)?array('$eq' => $tagsArray):array('$in' => $tagsArray));
			}

			if( @$key )
            	$params["source.key"] = $key ;
            if( @$insee )
            	$params["address.codeInsee"] = $insee ;

            if($limit > 500)
            	$limit = 500 ;
            else if($limit < 1)
            	$limit = 50 ;

          	if($index < 0)
            	$index = 0 ;
        }

        $data = PHDB::findAndLimitAndIndex($type , $params, $limit, $index);
        
        if(empty($id)){
        	$meta["limit"] = $limit;
        	$meta["next"] = "/ph/communecter/data/get/type/".$type."/limit/".$limit."/index/".($index+$limit);

        	if(@$format)
        		$meta["next"] .= "/format/".$format ;

        	if($index != 0){
        		$newIndex = $index - $limit;
        		if($newIndex < 0)
        			$newIndex = 0 ;
        		$meta["previous"] = "/ph/communecter/data/get/type/".$type."/limit/".$limit."/index/".$newIndex ;
        	}
        }else{
        	$meta["limit"] = 1;
        }

        $result["meta"] = $meta ;
        
        if($typeResult == "identity")
        	$result[$typeResult] = $data[Yii::app()->session["userId"]] ;
        else{
        	$val = array();
        	foreach ($data as $key => $value) {
            	if(!empty($value["preferences"]["publicFields"]) && in_array("isOpenData", $value["preferences"]["publicFields"])){
              		if($format == null || $format == "json"){
                		$value["links"] = self::getNewFormatLink($value["links"]);
              		}
              $val[$key] = $value ;
            }else{
            	//var_dump($value);
            	if(!empty($value["name"])){
					if($format != null && $format != "json")
            			$val[$key]["_id"] = $value["_id"];
              		$val[$key]["name"] = $value["name"];
            	}
            		
            }
          }
          $result[$typeResult] = $val;
        }  

        if($link == true){
        	$result["links"] = self::getNewFormatLink($data[$id]["links"]);
        	unset($result[$typeResult][$id]["links"]);
          
        }

        //var_dump($data);
        //var_dump($result[$typeResult]);
        if($result[$typeResult] && $bindMap )
          	$result[$typeResult] = Translate::convert($result[$typeResult] , $bindMap);
        return $result;
  }

	
	public static function getNewFormatLink($link){
		$fieldsLink = array("name");
		$allData = array();
		foreach ($link as $typeLinks => $valueLinks){
			foreach ($valueLinks as $keyLink => $valueLink){
				$paramsLink = array() ;
				$dataLink = PHDB::findOne($valueLink["type"], array("_id"=>new MongoId($keyLink)), $fieldsLink);
				if(!empty($dataLink)){
					$newFormatData["name"] = $dataLink["name"];
					$newFormatData["url"] = "/ph/communecter/data/get/type/".$valueLink["type"]."/id/".$keyLink ;

					if(!empty($valueLink["isAdmin"]))
                  		$newFormatData["isAdmin"] = $valueLink["isAdmin"] ;
                	
                	$allData[$typeLinks][] = $newFormatData;
                }
            }
        }
        return $allData ;
    }
	

}