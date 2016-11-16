<?php 
class Api {

    const COLLECTION = "gantts";
    
    public static function getData2($bindMap, $format = null, $type, $id = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null){

        $data = null;
        $typeResult = "entities";

        if($type == Person::COLLECTION && @$id && $id == Yii::app()->session["userId"]){
          
          $params["_id"] = new MongoId($id);
          $index = 0 ;
          $limit = 1 ;
          $typeResult = "identity";

        }else{
            $params = array();
            if( @$id ) 
                $params["_id"] =  new MongoId($id);
                
            if( @$insee ){
                if($type == City::COLLECTION)
                    $params["insee"] = $insee;
                else
                    $params["address.codeInsee"] = $insee ;
            }

            if( @$tags ){
                $tagsArray = explode(",", $tags);
                $params["tags"] =  (($multiTags == true)?array('$eq' => $tagsArray):array('$in' => $tagsArray));
            }

            if( @$key )
                $params["source.key"] = $key ;
            
            if($limit > 500)
                $limit = 500 ;
            else if($limit < 1)
                $limit = 50 ;

            if($index < 0)
                $index = 0 ;
        }
        
        $data = PHDB::findAndLimitAndIndex($type , $params, $limit, $index);
        
        $data = self::getUrlImage($data, $type);
       
        if($typeResult != "identity"){
            foreach ($data as $key => $value) {
                $isOpenData = ((empty($value["preferences"]))?false:Preference::isOpenData($value["preferences"]));  
                if($isOpenData == false){
                    $newData["name"] = ((empty($value["name"]))?"":$value["name"]);
                    $data[$key] = $newData ;
                }
            }
        }


        if(Person::COLLECTION == $type && $typeResult != "identity"){
            foreach ($data as $key => $value) {
                $person = Person::clearAttributesByConfidentiality($value);
                $data[$key] = $person ;
            }
        }
        

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
        
        if($typeResult == "identity"){
            /*if($link == true){
                $data[$id]["links"] = self::getNewFormatLink($data[$id]["links"]);
            }*/
            //var_dump($data);
            $result[$typeResult] = $data ;
            
        }
        else{
            /*$val = array();
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
          }*/
          //$result[$typeResult] = $val;
          $result[$typeResult] = $data ;
        }  

        
        
        
        //var_dump(json_encode($result[$typeResult]));
        if($result[$typeResult] && $bindMap )
            $result[$typeResult] = Translate::convert($result[$typeResult] , $bindMap);

        //var_dump($result[$typeResult]);
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
                    $newFormatData["url"] = "/data/get/type/".$valueLink["type"]."/id/".$keyLink ;

                    if(!empty($valueLink["isAdmin"]))
                        $newFormatData["isAdmin"] = $valueLink["isAdmin"] ;
                    
                    $allData[$typeLinks][] = $newFormatData;
                }
            }
        }
        return $allData ;
    }

    
    public static function getUrlImage($data, $type){
        foreach ($data as $keyEntities => $valueEntities) {
            $doc = Document::getLastImageByKey($keyEntities, $type, Document::IMG_PROFIL) ;
            $data[$keyEntities]["image"] = $doc ;
        }
        return $data;
    }


    public static function getData($bindMap, $format = null, $type, $id = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null){
        
        // Create params for request
        $params = array();
        if( @$id ) 
            $params["_id"] =  new MongoId($id);
            
        if( @$insee ){
            if($type == City::COLLECTION)
                $params["insee"] = $insee;
            else
                $params["address.codeInsee"] = $insee ;
        }

        if( @$tags ){
            $tagsArray = explode(",", $tags);
            $params["tags"] =  (($multiTags == true)?array('$eq' => $tagsArray):array('$in' => $tagsArray));
        }

        if( @$key )
            $params["source.key"] = $key ;
        
        if( $limit > 500)
            $limit = 500 ;
        else if($limit < 1)
            $limit = 50 ;

        if($index < 0)
            $index = 0 ;

        $params["preferences.isOpenData"] = true ;

        $data = PHDB::findAndLimitAndIndex($type , $params, $limit, $index);
        $data = self::getUrlImage($data, $type);

        if(Person::COLLECTION == $type){
            foreach ($data as $key => $value) {
                $person = Person::clearAttributesByConfidentiality($value);
                $data[$key] = $person ; 
            }
        }

        // create JSON
        if(empty($id)){
            $meta["limit"] = $limit;
            $meta["next"] = "/ph/communecter/data/get/type/".$type."/limit/".$limit."/index/".($index+$limit);

            if(@$format)
                $meta["format"] = "/format/".$format ;
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
        $result["entities"] = ((!empty($data) && !empty($bindMap) )?Translate::convert($data , $bindMap):$data);

        return $result;
    }
    
    
    /**
    * Returns a string with accent to REGEX expression to find any combinations
    * in accent insentive way
    *
    * @param string $text The text.
    * @return string The REGEX text.
    */

    static public function accentToRegex($text)
    {

        $from = str_split(utf8_decode('ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿ'));
        $to   = str_split(strtolower('SOZsozYYuAAAAAAACEEEEEIIIIIDNOOOOOOUUUUYsaaaaaaaceeeeeiiiiionoooooouuuuyy'));
        //‘ŠŒŽšœžŸ¥µÀÁÂÃÄÅÆÇÈÉÊËẼÌÍÎÏĨÐÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåæçèéêëẽìíîïĩðñòóôõöøùúûüýÿaeiouçAEIOUÇ';
        //‘SOZsozYYuAAAAAAACEEEEEIIIIIDNOOOOOOUUUUYsaaaaaaaceeeeeiiiiionoooooouuuuyyaeioucAEIOUÇ';
        $text = utf8_decode($text);
        $regex = array();

        foreach ($to as $key => $value)
        {
            if (isset($regex[$value]))
                $regex[$value] .= $from[$key];
            else 
                $regex[$value] = $value;
        }

        foreach ($regex as $rg_key => $rg)
        {
            $text = preg_replace("/[$rg]/", "_{$rg_key}_", $text);
        }

        foreach ($regex as $rg_key => $rg)
        {
            $text = preg_replace("/_{$rg_key}_/", "[$rg]", $text);
        }
        return utf8_encode($text);
    }
}