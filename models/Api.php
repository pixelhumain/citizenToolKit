<?php 
class Api {

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

    public static function getDataBySearch($search, $type, $tags, $index, $limit){
        /*$search = "", $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null)
        $search = @$post['name'] ? trim(urldecode($post['name'])) : "";
        $locality = isset($post['locality']) ? trim(urldecode($post['locality'])) : null;
        //$localities = isset($post['localities']) ? $post['localities'] : null;
        $searchType = explode(",", $type);
        $searchTags = isset($post['searchTag']) ? $post['searchTag'] : null;
        
        $indexMin = isset($post['indexMin']) ? $post['indexMin'] : 0;
        $indexMax = isset($post['indexMax']) ? $post['indexMax'] : 30;
        $country = isset($post['country']) ? $post['country'] : "";
        $priceMin = isset($_POST['priceMin']) ? $_POST['priceMin'] : null;
        $priceMax = isset($_POST['priceMax']) ? $_POST['priceMax'] : null;
        $devise = isset($_POST['devise']) ? $_POST['devise'] : null;
        $latest = isset($_POST['latest']) ? $_POST['latest'] : null;
        $searchSType = !empty($post['searchSType']) ? $post['searchSType'] : "";

        $search = "", $format = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null*/
        $paramsSearch = array(  'name' => $search,
                                'searchType' => ( !empty($type) ? explode(",", $type) : array(Person::COLLECTION, Organization::COLLECTION, Project::COLLECTION)),
                                'searchTag' => ( !empty($tags) ? explode(",", $tags) : array()),
                                'indexMin' => ( !empty($index) ? $index : "0"),
                                'indexMax' => ( !empty($limit) ? $index+$limit : "30") ) ;
                                /*'country' => $name
                                'priceMin' => $name
                                'priceMax' => $name
                                'devise' => $name
                                'latest' => $name
                                'searchSType' => $name*/
        $res = Search::globalAutoComplete($paramsSearch, null, true);
        return $res;
    }


    public static function getData($bindMap, $format = null, $type, $id = null, $limit=50, $index=0, $tags = null, $multiTags=null , $key = null, $insee = null, $geoShape = null, $idElement = null, $typeElement = null){
        
        // Create params for request
        $params = array();
        if( @$id ) $params["_id"] =  new MongoId($id);

        //"target.id": "589094538fe7a1d3048b4587"
        if( @$idElement ) $params["target.id"] = $idElement;
        //"target.type": "organizations" 
        if( @$typeElement) $params["target.type"] = $typeElement;
            
        if( @$insee ){
            if($type == City::COLLECTION) $params["insee"] = $insee;
            else $params["address.codeInsee"] = $insee ;
        }
        
        if( @$tags ){
            $tagsArray = explode(",", $tags);
            $params["tags"] =  (($multiTags == "true") ? array('$eq' => $tagsArray) : array('$in' => $tagsArray));
        }

        if( $type == New::COLLECTION ){
            $params["scope.type"] = "public";
        }

        if( @$key ) $params["source.key"] = $key ;
        
        if( $limit > 500) $limit = 500 ;
        else if($limit < 1) $limit = 50 ;

        if($index < 0) $index = 0 ;
        //if($type != City::COLLECTION) $params["preferences.isOpenData"] = true ;

        $data = PHDB::findAndLimitAndIndex($type , $params, $limit, $index);
        $data = self::getUrlImage($data, $type);

        if($type == City::COLLECTION) {
            if( @$geoShape != "1" ){
                foreach ($data as $key => $value) {
                    unset($value["geoShape"]);
                    $data[$key] = $value ; 
                }
            }
        }

        if(Person::COLLECTION == $type){
            foreach ($data as $key => $value) {
                $person = Person::getSimpleUserById($key, $value);
                $data[$key] = $person ; 
            }
        }

        if (( $format == Translate::FORMAT_RSS) || ($format == Translate::FORMAT_KML) || ($format == Translate::FORMAT_KML)) {
            //if(((@$idElement) && (@$typeElement)) || (@$tags)) {
            foreach ($data as $key => $value) {

                $data2[] = $value ;                    
            }

            if (isset($data2)) {
                $data = $data2;
            }

            //}
        }

        // create JSON
        if(empty($id)){
            $meta["limit"] = $limit;
            $server = ((isset($_SERVER['HTTPS']) AND (!empty($_SERVER['HTTPS'])) AND strtolower($_SERVER['HTTPS'])!='off') ? 'https://' : 'http://').$_SERVER['HTTP_HOST'];
            $meta["next"] = $server.Yii::app()->createUrl("/api/".Element::getControlerByCollection($type)."/get/limit/".$limit."/index/".($index+$limit));

            if(@$format)
                $meta["format"] = "/format/".$format ;
            if($index != 0){
                $newIndex = $index - $limit;
                if($newIndex < 0)
                    $newIndex = 0 ;
                $meta["previous"] = $server.Yii::app()->createUrl("/api/".Element::getControlerByCollection($type)."/get/limit/".$limit."/index/".$newIndex) ;
            }
        }else{
            $meta["limit"] = 1;

        }

        if (($format == Translate::FORMAT_RSS) || ($format == Translate::FORMAT_KML)) {
            $result = ((!empty($data) && !empty($bindMap) )?Translate::convert($data , $bindMap):$data);         
        } 
        elseif ($format == Translate::FORMAT_GEOJSON) {
            $result["type"] = "FeatureCollection";
            $result["features_temp"] = ((!empty($data) && !empty($bindMap) )?Translate::convert_geojson($data , $bindMap):$data);
            $result["features"] = array();
            $result["features"] = $result["features_temp"];
            unset($result["features_temp"]);
        } elseif ($format == Translate::FORMAT_JSONFEED) {
            $meta = [];
            $meta["version"] = "https://jsonfeed.org/version/1";
            $meta["title"] = "Communecter's JSON Feed";
            $meta["description"] = "This is the JSON Feed of the Communecter site";
            $meta["home_page_url"] = "www.communecter.org";

            $result["meta"] = $meta ;
            $result["items"] = ((!empty($data) && !empty($bindMap) )?Translate::convert($data , $bindMap):$data);
        }
        else { 
            $result["meta"] = $meta ;
            $result["entities"] = ((!empty($data) && !empty($bindMap) )?Translate::convert($data , $bindMap):$data);

        }
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