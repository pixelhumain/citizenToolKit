<?php
/*
    
 */
class Import
{ 
    const MAPPINGS = "mappings";
    public static function parsing($post) {
        $params = array("result"=>false);
        if($post['typeFile'] == "json" || $post['typeFile'] == "js" || $post['typeFile'] == "geojson")
            $params = self::parsingJSON($post);
        else if($post['typeFile'] == "csv")
            $params = self::parsingCSV($post); 
        else if($post['typeFile'] == "xml")
            $params = self::parsingXML($post);
        return $params ;
    }

    public static function parsingCSV($post) {
        
        $attributesElt = ArrayHelper::getAllPathJson(file_get_contents("../../modules/co2/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH));
        if($post['idMapping'] != "-1"){
            $where = array("_id" => new MongoId($post['idMapping']));
            $fields = array();
            $mapping = self::getMappings($where, $fields);
            //Remplace les "_dot_" par des "."
            $mapping[$post['idMapping']]["fields"] = Mapping::replaceByRealDot($mapping[$post['idMapping']]["fields"]);
            $arrayMapping = $mapping[$post['idMapping']]["fields"];
        }
        else
            $arrayMapping = array();
        $params = array("result"=>true,
                        "attributesElt"=>$attributesElt,
                        "arrayMapping"=>$arrayMapping,
                        "idMapping"=>$post['idMapping'],
                        "mapping"=> $mapping[$post['idMapping']],
                        "typeFile"=>$post['typeFile']);
        return $params ;
    }

    public static function parsingJSON($post){
        header('Content-Type: text/html; charset=UTF-8');
        $params = array("result"=>false);
        if(isset($post['file'])) {
            
            $json = $post['file'][0];
            //(empty($post["path"]) ? $post['file'][0] : $post['file'][0][$post["path"]]);
            // if (isset($post['path'])) {
            //     $json = $post['file'][0][$post['path']][0];
            // } 
            if($post['idMapping'] != "-1"){
                
                $where = array("_id" => new MongoId($post['idMapping']));
                $fields = array();
                $mapping = self::getMappings($where, $fields);
                $mapping[$post['idMapping']]["fields"] = Mapping::replaceByRealDot($mapping[$post['idMapping']]["fields"]);
                $arrayMapping = $mapping[$post['idMapping']]["fields"];
            }
            else
                $arrayMapping = array();
            if(!empty($post["path"])){
                $json = json_decode($json,true);
                $json = $json[$post["path"]];
                $json = json_encode($json);
            }
            if(substr($json, 0,1) == "{")
                $arbre = ArrayHelper::getAllPathJson($json); 
            else{
                $arbre = array();
                foreach (json_decode($json,true) as $key => $value) {
                    $arbre = ArrayHelper::getAllPathJson(json_encode($value), $arbre); 
                }
            }
            $attributesElt = ArrayHelper::getAllPathJson(file_get_contents("../../modules/co2/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH));
            $params = array("result"=>true,
                        "attributesElt"=>$attributesElt,
                        "arrayMapping"=>$arrayMapping,
                        "idMapping"=>$post['idMapping'],
                        "mapping"=> $mapping[$post['idMapping']],
                        "arbre"=>$arbre,
                        "typeFile"=>$post['typeFile']);          
        }
        return $params ;
    }

     public static function parsingXML($post)
    {
        $params = array("result" => false);

        if(isset($post['file']))
        {

            $arbre = array();
            $contenu = array();
            $file = simplexml_load_string($post['file'][0]);
            $n = 0;
            $json = ['elements' => [$file]];
            $json= json_encode($json,true);

            foreach($file->children() as $child){
                $arbre[$n] = $child->getName();
                $n++;
            }

            if($post['idMapping'] != "-1"){
                $where = array("_id" => new MongoId($post['idMapping']));
                $fields = array("fields");
                $mapping = self::getMappings($where, $fields);
                $mapping[$post['idMapping']]["fields"] = Mapping::replaceByRealDot($mapping[$post['idMapping']]["fields"]);
                $arrayMapping = $mapping[$post['idMapping']]["fields"];
                $find = PHDB::findOne(self::MAPPINGS,$where);
            }
            else
                $arrayMapping = array();

            $attributesElt = ArrayHelper::getAllPathJson(file_get_contents("../../modules/co2/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH));

            if($post['idMapping'] != "-1"){
                $params = array("result" => true,
                    "attributesElt" => $attributesElt,
                    "arrayMapping" => $arrayMapping,
                    "arbre" => $arbre,
                    "json" => $json,
                    "typeFile" => $post['typeFile'],
                    "idMapping" => $post['idMapping'],
                    "nameUpdate" => $find['name']);
            }

            else{
                    $params = array("result" => true,
                    "attributesElt" => $attributesElt,
                    "arrayMapping" => $arrayMapping,
                    "arbre" => $arbre,
                    "json" => $json,
                    "typeFile" => $post['typeFile'],
                    "idMapping" => $post['idMapping']);
            }
    }
        return $params;
    }


    public static function getMappings($where=array(),$fields=null){
        $allMappings = PHDB::find(self::MAPPINGS, $where, $fields);
        $allMappings = self::getStandartMapping($allMappings);
        return $allMappings;
    }
   
    public static  function previewData($post, $notCheck=false){

        $params = array("result"=>false);
        $elements = array();
        $saveCities = array();
        $nb = 0 ;
        $elementsWarnings = array();
        if(!empty($post['infoCreateData']) && !empty($post['file']) && !empty($post['nameFile']) && !empty($post['typeFile'])){
            $mapping = json_decode(file_get_contents("../../modules/co2/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH), true);
            $attributesElt = ArrayHelper::getAllPathJson(file_get_contents("../../modules/co2/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH));
            if($post['typeFile'] == "csv"){
                $file = $post['file'];
                $headFile = $file[0];
                unset($file[0]);
            }elseif ((!isset($post['pathObject'])) || ($post['pathObject'] == "")) {
                $file = json_decode($post['file'][0], true);
            }else {
                $file = json_decode($post['file'][0], true);
                $file = @$file[$post["pathObject"]];
            }
            
            if(@$file)
            foreach ($file as $keyFile => $valueFile){
                $nb++;
                //if(!empty($valueFile)){
                    $element = array();     
                    foreach ($post['infoCreateData'] as $key => $value) {
                        $valueData = null;
                        if($post['typeFile'] == "csv" && in_array($value["idHeadCSV"], $headFile)){
                            $idValueFile = array_search($value["idHeadCSV"], $headFile);
                            $valFile =  (!empty($valueFile[$idValueFile])?$valueFile[$idValueFile]:null);
                        }else if ($post['typeFile'] == "json" || $post['typeFile'] == "xml"){
                            $valFile =  ArrayHelper::getValueByDotPath($valueFile , $value["idHeadCSV"]);
                            // var_dump($valFile);
                        }
                        else{
                            $valFile =  (!empty($valueFile[$value["idHeadCSV"]])?$valueFile[$value["idHeadCSV"]]:null);
                        }
                        if(!empty($valFile)){
                            $valueData = (is_string($valFile)?trim($valFile):$valFile);
                            if(!empty($valueData)){
                                $typeValue = ArrayHelper::getValueByDotPath($mapping , $value["valueAttributeElt"]);
                                $element = ArrayHelper::setValueByDotPath($element , $value["valueAttributeElt"], $valueData, $typeValue);
                            }
                        }
                    }
                    $element['source']['insertOrign'] = "import";
                    if(!empty($post['key'])){
                        $element['source']['keys'][] = $post['key'];
                        $element['source']['key'] = $post['key'];
                    }
                    if($notCheck != true)
                        $element = self::checkElement($element, $post['typeElement']);
                    if(!empty($element["saveCities"])){
                        $saveCities[] = $element["saveCities"];
                        unset($element["saveCities"]);
                    }
                    if($post['typeElement'] == Person::COLLECTION){
                        $element['msgInvite'] = $post['msgInvite'];
                        $element['nameInvitor'] = $post['nameInvitor'];
                    }
                    if(!empty($element['msgError']) || ($post['warnings'] == "false" && !empty($element['warnings'])))
                        $elementsWarnings[] = $element;
                    else
                        $elements[] = $element;
                //}
            }
            
            $params = array("result"=>true,
                            "elements"=>json_encode(json_decode(json_encode($elements),true)),
                            "elementsWarnings"=>json_encode(json_decode(json_encode($elementsWarnings),true)),
                            "saveCities"=>json_encode(json_decode(json_encode($saveCities),true)),
                            "listEntite"=>self::createArrayList(array_merge($elements, $elementsWarnings)));
        }
        return $params;
    }  
    public static function createArrayList($list) {
        $head = array("name", "address", "warnings", "msgError") ;
        $tableau = array($head);
        foreach ($list as $keyList => $valueList){
            $ligne = array();
            $ligne[] = (empty($valueList["name"])? "" : $valueList["name"]);
            if(!empty($valueList["address"])){
                $str = (empty($valueList["address"]["streetAddress"]) ? "" : $valueList["address"]["streetAddress"].",");
                $str .= (empty($valueList["address"]["postalCode"]) ? "" : $valueList["address"]["postalCode"].", ");
                $str .= (empty($valueList["address"]["addressLocality"]) ? "" : $valueList["address"]["addressLocality"].", ");
                $str .= (empty($valueList["address"]["addressCountry"]) ? "" : $valueList["address"]["addressCountry"]);
                $ligne[] = $str;
            }
            $ligne[] = (empty($valueList["warnings"])? "" : self::getMessagesWarnings($valueList["warnings"]));
            $ligne[] = (empty($valueList["msgError"])? "" : $valueList["msgError"]);
            $tableau[] = $ligne ;
        }
        return $tableau ;
    }
    public static function getMessagesWarnings($warnings){
        $msg = "";
        foreach ($warnings as $key => $codeWarning) {
            if($msg != "")
                $msg .= "<br/>";
            $msg .= Yii::t("import",$codeWarning);
        }
        return $msg;
    }
    public static function checkElement($element, $typeElement){
        //Rest::json($element); exit;

        $result = array("result" => true);
        $geo = (empty($element['geo']) ? null : $element['geo']);
        if($typeElement != Person::COLLECTION ) {
            $address = (empty($element['address']) ? null : $element['address']);
            $geo = (empty($element['geo']) ? null : $element['geo']);

            if(!empty($address) && !empty($address["addressCountry"])  && !empty($address["postalCode"]) && strtoupper($address["addressCountry"]) == "FR" && strlen($address["postalCode"]) == 4 )
                $address["postalCode"] = '0'.$address["postalCode"];
                $detailsLocality = self::getAndCheckAddressForEntity($address, $geo) ;
            
            if($detailsLocality["result"] == true){
                $element["address"] = $detailsLocality["address"] ;
                $element["geo"] = $detailsLocality["geo"] ;
                $element["geoPosition"] = $detailsLocality["geoPosition"] ;
                if(!empty($detailsLocality["saveCities"]))
                    $saveCities = $detailsLocality["saveCities"] ; 

                //Rest::json($element); exit;
            }
        }
        if($typeElement == Event::COLLECTION || $typeElement == Project::COLLECTION){
            date_default_timezone_set('UTC');
            if(!empty($element['startDate']))
                $element['startDate'] = date('Y-m-d H:i:s', strtotime($element['startDate']));
            
            if(!empty($element['endDate']))
                $element['endDate'] = date('Y-m-d H:i:s', strtotime($element['endDate']));
        }

        if(!empty($element["tags"]))
            $element["tags"] = self::checkTag($element["tags"]);
        
        
        if($typeElement == Organization::COLLECTION && !empty($element["type"]))
            $element["type"] = Organization::translateType($element["type"]);
        if(!empty($element["facebook"]))
            $element["socialNetwork"]["facebook"] = $element["facebook"];
        if ($element['source']['keys'][0] !== "convert_datagouv" && $element['source']['keys'][0] !== "convert_osm" && $element['source']['keys'][0] !== "convert_ods" && $element['source']['keys'][0] !== "convert_wiki" && $element['source']['keys'][0] !== "convert_datanova" && $element['source']['keys'][0] !== "convert_poleemploi" && $element['source']['keys'][0] !== "convert_educ_etab" && $element['source']['keys'][0] !== "convert_educ_membre" && $element['source']['keys'][0] !== "convert_educ_ecole" && $element['source']['keys'][0] !== "convert_educ_struct" && $element['source']['keys'][0] !== "convert_valueflows" && $element['source']['keys'][0] !== "convert_organcity") {
            $element = self::getWarnings($element, $typeElement, true) ;
        }
        $resDataValidator = DataValidator::validate(Element::getControlerByCollection($typeElement), $element, true);
        if($resDataValidator["result"] != true){
            //$element["msgError"] = ((empty($resDataValidator["msg"]->getMessage()))?$resDataValidator["msg"]:$resDataValidator["msg"]->getMessage());
            $element["msgError"] = $resDataValidator["msg"];
        }
        if(!empty($saveCities))
            $element["saveCities"] = $saveCities ; 
        
        return $element;
    }
    public static function checkTag($tags){
        $newTags = array();
        foreach ($tags as $key => $tag) {
           $split = explode(",", $tag);
           foreach ($split as $keyS => $value) {
               $newTags[] = trim($value);
           }
        }
        return $newTags;
    }
    public static function getAddressConform($city, $address){
        //Rest::json($address); exit;

        $newA = array(
                '@type' => 'PostalAddress',
                'addressCountry' =>  $city["country"]);
        if(!empty($city["_id"])){
            $newA = array(
                '@type' => 'PostalAddress',
                'addressCountry' =>  strtoupper($city["country"]),
                'localityId' =>  (String) $city["_id"],
                'level1' =>  $city["level1"],
                'level1Name' =>  $city["level1Name"]);
        }else{
            $newA["osmID"] = $city["osmID"];
        }

        if( !empty($address["postalCode"]) &&  !empty($city["postalCodes"]) ) {
            foreach ($city["postalCodes"] as $keyCp => $valueCp){
                if($valueCp["postalCode"] == $address["postalCode"]){
                    $newA['addressLocality'] = $valueCp["name"];
                    $newA['postalCode'] = $address["postalCode"];
                }
            }
            if(empty($newA['addressLocality'])){
                $newA['addressLocality'] = $address["addressLocality"];
                // TODO RAPHA : FAIRE EN SORT AJOUER LES CEDEXS DANS LES VILLES 
            }

        }else if( empty($address["postalCode"]) && !empty($city["postalCodes"]) ) {
            $newA['addressLocality'] = $city["postalCodes"][0]["name"];
            $newA['postalCode'] = $city["postalCodes"][0]["postalCode"];
            
        }else{
            $newA["addressLocality"] = $city["name"];
        }

        if( !empty($address["streetAddress"]) ) 
            $newA["streetAddress"] = $address["streetAddress"];
        if( !empty($city["insee"]) )
            $newA["codeInsee"] = $city["insee"];
                        
        if( !empty($city["level2"]) ) {
            $newA["level2"] = $city["level2"];
            $newA["level2Name"] = $city["level2Name"];
        }
        if( !empty($city["level3"]) ) {
            $newA["level3"] = $city["level3"];
            $newA["level3Name"] = $city["level3Name"];
        }
        if( !empty($city["level4"]) ) {
            $newA["level4"] = $city["level4"];
            $newA["level4Name"] = $city["level4Name"];
        }

        //Rest::json($newA); exit;
        return $newA ;
    }
    public static function getLatLonBySIG($address){
        $street = (empty($address["streetAddress"])?null:$address["streetAddress"]);
        $cp = (empty($address["postalCode"])?null:$address["postalCode"]);
        $geo = array();
        $resultDataGouv = ( ( !empty($address["addressCountry"]) && $address["addressCountry"] == "FR" ) ? ( empty($cp) ? null : json_decode(SIG::getGeoByAddressDataGouv($street, $cp, $address["addressLocality"]), true) ) : null ) ;
        if(!empty($resultDataGouv["features"])){
            $geo["lat"] = strval($resultDataGouv["features"][0]["geometry"]["coordinates"][1]);
            $geo["lon"] = strval($resultDataGouv["features"][0]["geometry"]["coordinates"][0]);
        }else{
            $resultNominatim = json_decode(SIG::getGeoByAddressNominatim($street, $cp, $address["addressLocality"], $address["addressCountry"]), true);
            if(!empty($resultNominatim[0])){
                $geo["lat"] = $resultNominatim[0]["lat"];
                $geo["lon"] = $resultNominatim[0]["lon"];
            }else{
                $resultGoogle = json_decode(SIG::getGeoByAddressGoogleMap($street, $cp, $address["addressLocality"], $address["addressCountry"]), true);
                $resG = false ;
                if(!empty($resultGoogle["results"][0]["address_components"])){
                    foreach ($resultGoogle["results"][0]["address_components"] as $key => $value) {
                        if(in_array("locality", $value["types"]))
                            $resG = true ;
                    }
                }
                if(!empty($resultGoogle["results"]) && $resG == true){
                    $geo["lat"] = strval($resultGoogle["results"][0]["geometry"]["location"]["lat"]);
                    $geo["lon"] = strval($resultGoogle["results"][0]["geometry"]["location"]["lng"]);
                }
            }
        }
        return $geo ;
    }



	public static function getAndCheckAddressForEntity($address = null, $geo = null){
		$lat = null;
		$lon = null;
		$result = false;
		$saveCities = array();
        //Rest::json($address); exit;
        //Rest::json($geo); exit;
		if( !empty($address["addressLocality"]) && 
            !empty($address["addressCountry"]) ) {
            $city = null ;
            if(!empty($address["codeInsee"])){
                $where = array('$and' => array(
                                array("insee" => strtoupper($address["codeInsee"])), 
                                array("country" => strtoupper($address["addressCountry"])) ) );
                $city = PHDB::findOne(City::COLLECTION, $where, $fields);
            }
            if( stripos($address["addressLocality"], "CEDEX") !== false && $address["addressCountry"] == "FR" ){
                $local = trim(str_replace("CEDEX", "", $address["addressLocality"]));
                $regexCity = Search::accentToRegex(strtolower($local));
            }
            else
                $regexCity = Search::accentToRegex(strtolower($address["addressLocality"]));

            if(empty($city)){


                $where = array('$or'=> 
                        array(  
                            array("name" => new MongoRegex("/^".$regexCity."/i")),
                            array("alternateName" => new MongoRegex("/^".$regexCity."/i")),
                            array("postalCodes.name" => new MongoRegex("/^".$regexCity."/i"))
                        ) );
                $where = array('$and' => array($where, array("country" => strtoupper($address["addressCountry"])) ) );
                
                if( !empty($address["postalCode"]) && !(stripos($address["addressLocality"], "CEDEX") !== false && $address["addressCountry"] == "FR") ){
                    $where = array('$and' => array($where, array("postalCodes.postalCode" => $address["postalCode"]) ) );
                }

                $fields = array("name", "geo", "country", "level1", "level1Name","level2", "level2Name","level3", "level3Name","level4", "level4Name", "osmID", "postalCode", "insee");

                // Rest::json($where); exit;

                $city = PHDB::findOne(City::COLLECTION, $where, $fields);
            }
            if(!empty($city)){

                if(empty($geo["latitude"]) || empty($geo["longitude"])){
                    $resGeo = self::getLatLonBySIG($address);
                    $lat = ( empty($resGeo["lat"]) ? $city["geo"]["latitude"] : $resGeo["lat"] );
                    $lon = ( empty($resGeo["lon"]) ? $city["geo"]["longitude"] : $resGeo["lon"] );
                }else{
                    $lat = $geo["latitude"] ;
                    $lon = $geo["longitude"];
                }
                $newA = self::getAddressConform($city, $address);
                $newGeo = SIG::getFormatGeo($lat, $lon);
                $newGeoPosition = SIG::getFormatGeoPosition($lat, $lon);
                $result = true;
            }
        } 

        //Rest::json($newA); exit;
        //Rest::json($newGeo); exit;


        if(empty($newA) && !empty($geo["latitude"]) && !empty($geo["longitude"])){
            $lat = ( is_numeric($geo["latitude"]) ? strval($geo["latitude"]) : $geo["latitude"] ) ;
            $lon = ( is_numeric($geo["longitude"]) ? strval($geo["longitude"]) : $geo["longitude"] ) ;
            $city = SIG::getCityByLatLngGeoShape( $lat, $lon, null, (!empty($address["addressCountry"]) ? $address["addressCountry"] : null ) ) ;

            
            if(!empty($city)){
                $newA = self::getAddressConform($city, $address);
                $newGeo = SIG::getFormatGeo($lat, $lon);
                $newGeoPosition = SIG::getFormatGeoPosition($lat, $lon);
                $result = true;
            }      
        }


        if(empty($newA) ){
        	$resNominatim = json_decode(SIG::getGeoByAddressNominatim(null, null, $address["addressLocality"], trim($address["addressCountry"]), false, true),true);
            if(!empty($resNominatim)){
                $typeCities = array("city", "village", "town") ;
                foreach ($resNominatim as $keyN=> $valueN) {
                    $break = false ;
                    foreach ($typeCities as $keyT=> $valueT) {
                        if( !empty($valueN["address"][$valueT]) && 
                            $address["addressCountry"] == strtoupper(@$valueN["address"]["country_code"])) {   
                                $saveCities = array(    "name" => $valueN["address"][$valueT],
                                                        "alternateName" => mb_strtoupper($valueN["address"][$valueT]),
                                                        "country" => (!empty($address["addressCountry"]) ? $address["addressCountry"] :  strtoupper($valueN["address"]["country_code"])),
                                                        "geo" => SIG::getFormatGeo($valueN["lat"], $valueN["lon"]),
                                                        "geoPosition" =>  SIG::getFormatGeoPosition($valueN["lat"], $valueN["lon"]),
                                                        "level3Name" => (empty($valueN["address"]["state"]) ? null : $valueN["address"]["state"] ),
                                                        "level3" => null,
                                                        "level4Name" => (empty($valueN["address"]["county"]) ? null : $valueN["address"]["county"] ),
                                                        "level4" => null,
                                                        "osmID" => $valueN["osm_id"],
                                                        "save" => true);
                                    if(!empty($valueN["extratags"]["wikidata"]))
                                    $saveCities["wikidataID"] = $valueN["extratags"]["wikidata"];
                                $newA = self::getAddressConform($saveCities, $address);

                                if(!empty($valueT["lat"]) && !empty($valueT["lon"])){
                                	$newGeo = SIG::getFormatGeo($valueT["lat"], $valueT["lon"]);
                                	$newGeoPosition = SIG::getFormatGeoPosition($valueT["lat"], $valueT["lon"]);
                                }
                                
                                $result = true;
                                break;
                        }
                    }
                    if($result == true)
                        break;
                }
            }

            if(empty($newA) && !empty($geo["latitude"]) && !empty($geo["longitude"]) ){
            	$lat = ( is_numeric($geo["latitude"]) ? strval($geo["latitude"]) : $geo["latitude"] ) ;
            	$lon = ( is_numeric($geo["longitude"]) ? strval($geo["longitude"]) : $geo["longitude"] ) ;
        		$resNominatim = json_decode(SIG::getLocalityByLatLonNominatim($lat, $lon),true, true);
               	if(!empty($resNominatim)){
                    $nameCity = self::getCityNameInNominatim($resNominatim["address"]);
                    if(!empty($nameCity)){
                        
                        $saveCities = array( "name" => $resNominatim["address"]["city"],
                                            "alternateName" => mb_strtoupper($resNominatim["address"]["city"]),
                                            "country" => (!empty($address["addressCountry"]) ? $address["addressCountry"] :  strtoupper($resNominatim["address"]["country_code"])),
                                            "geo" => SIG::getFormatGeo($lat, $lon),
                                            "geoPosition" =>  SIG::getFormatGeoPosition($lat, $lon),
                                            "level3Name" => (empty($resNominatim["address"]["state"]) ? null : $resNominatim["address"]["state"] ),
                                            "level3" => null,
                                            "level4Name" => (empty($resNominatim["address"]["county"]) ? null : $resNominatim["address"]["county"] ),
                                            "level4" => null,
                                            "osmID" => $resNominatim["osm_id"],
                                            "save" => true);
                        if(!empty($resNominatim["extratags"]["wikidata"]))
                            $saveCities["wikidataID"] = $resNominatim["extratags"]["wikidata"];
                        $newA = self::getAddressConform($saveCities, $address);
                        if(!empty($valueT["lat"]) && !empty($valueT["lon"])){
                        	$newGeo = SIG::getFormatGeo($valueT["lat"], $valueT["lon"]);
                        	$newGeoPosition = SIG::getFormatGeoPosition($valueT["lat"], $valueT["lon"]);
                        }
                        $result = true;
                    }
                }
            }
        }
        //Rest::json($newGeo); exit;
        $res = array(   "result" => $result,
                        "address" => ( empty($newA) ? null : $newA),
                        "geo" => ( empty($newGeo) ? null : $newGeo),
                        "geoPosition" => ( empty($newGeoPosition) ? null : $newGeoPosition),
                        "saveCities" => ( empty($saveCities) ? null : $saveCities) );
        //Rest::json($res); exit;
        return $res ;
    }
    public static function getCityNameInNominatim($address){
        $typeCities = array("city", "village", "town") ;
        foreach ($typeCities as $key => $value) {
            if(!empty($address[$value]))
                return $address[$value] ;
        }
        return null;
    }
    public static function getWarnings($element, $typeElement, $import = null){
        $warnings = array();
        if(empty($element['name']))
            $warnings[] = "201" ;
        if(empty($element['email']) && $typeElement == Person::COLLECTION)
            $warnings[] = "203" ;



        if(empty($element['type']) && ( $typeElement == Organization::COLLECTION || $typeElement == Event::COLLECTION) )
            $warnings[] = "300" ;
        if($typeElement != Person::COLLECTION){
            if(!empty($element['address'])){
                if(empty($element['address']['addressCountry']))$warnings[] = "104" ;
                if(empty($element['address']['addressLocality'])) $warnings[] = "105" ;
                if(!empty($element['geo'])){
                    if(empty($element['geo']['latitude'])) $warnings[] = "151" ;
                    if(empty($element['geo']['longitude'])) $warnings[] = "152" ;
                }else if(!empty($element['address']['localityId'])) {
                    $warnings[] = (empty($import)?"150":"154") ;
                }
            }else{
                $warnings[] = (empty($import)?"100":"110");
            }
        }
        
        //var_dump($warnings);
        if(!empty($warnings))
            $element["warnings"] = $warnings;
        return $element;
    }
    public static function initMappings(){
        $mappings = json_decode(file_get_contents("../../modules/co2/data/import/mappings.json", FILE_USE_INCLUDE_PATH), true);
        foreach ($mappings as $key => $value) {
            PHDB::insert( Import::MAPPINGS, $value );
        }
    }
     public static function addDataInDb($post)
    {
        $jsonString = $post["file"];
        $typeElement = $post["typeElement"];
        /*$pathFolderImage = $post["pathFolderImage"];
        $sendMail = ($post["sendMail"] == "false"?null:true);
        $isKissKiss = ($post["isKissKiss"] == "false"?null:true);
        $invitorUrl = (trim($post["invitorUrl"]) == ""?null:$post["invitorUrl"]);*/
        
        if(substr($jsonString, 0,1) == "{")
            $jsonArray[] = json_decode($jsonString, true) ;
        else
            $jsonArray = json_decode($jsonString, true) ;
        if(isset($jsonArray)){
            $resData =  array();
            foreach ($jsonArray as $key => $value){
                try{
                    if($typeElement == City::COLLECTION){
                        $exist = City::alreadyExists($value, $typeElement);
                        if(!$exist["result"]) {
                            $res = City::insert($value, Yii::app()->session["userId"]);
                            $element["name"] = $value["name"];
                            $element["info"] = $res["msg"];
                        }else{
                            $element["name"] = $exist["city"]["name"];
                            $element["info"] = "La ville existes déjà";
                        }
                    }else{
//<<<<<<< savMap
//                        if( !empty( $value["address"] ) ) {
//                            $good = true ;
//                            if(!empty($value["address"]["osmID"])){
//                                $city = City::getByOsmId($value["address"]["osmID"]);
//                                if(!empty($city)){
//                                    $value["address"] = self::getAddressConform($city, $value["address"]);
////                                    $resGeo = self::getLatLonBySIG($value["address"]);
//                                    $value["geo"] = SIG::getFormatGeo($resGeo["lat"], $resGeo["lon"]);
//                                    $value["geoPosition"] = SIG::getFormatGeoPosition($resGeo["lat"], $resGeo["lon"]);
//                                }
//                                else{
//                                    $good = false ;
//                                    $element["name"] = $exist["element"]["name"];
//                                    $element["info"] = "La commune n'existe pas, penser a l'ajouter avants"; 
//                                }
//=======

						if( !empty( $value["address"] ) ) {
							$good = true ;
							if(!empty($value["address"]["osmID"])){
								$city = City::getByOsmId($value["address"]["osmID"]);

								if(!empty($city)){
									$value["address"] = self::getAddressConform($city, $value["address"]);
									$resGeo = self::getLatLonBySIG($value["address"]);
									if(!empty($resGeo)){
										$value["geo"] = SIG::getFormatGeo($resGeo["lat"], $resGeo["lon"]);
										$value["geoPosition"] = SIG::getFormatGeoPosition($resGeo["lat"], $resGeo["lon"]);
									}
									
								}
								else{
									$good = false ;
									$element["name"] = @$exist["element"]["name"];
									$element["info"] = "La commune n'existe pas, penser a l'ajouter avants"; 
								}
//>>>>>>> development
                            }
                            if($good == true){
                                $exist = Element::alreadyExists($value, $typeElement);
                                if(!$exist["result"]) {
                                    if(!empty($post["isLink"]) && $post["isLink"] == "true"){
                                        if($post["typeLink"] == Event::COLLECTION && $typeElement == Event::COLLECTION){
											$value["parentId"] = $post["idLink"];
											$value["parentType"] = $post["typeLink"];
                                        }
                                        else{
											$paramsLink["idLink"] = $post["idLink"];
											$paramsLink["typeLink"] = $post["typeLink"];
											$paramsLink["role"] = $post["roleLink"];
                                        }
                                        
                                    }
                                    if(!empty($value["urlImg"])){
                                        $paramsImg["url"] =$value["urlImg"];
                                        $paramsImg["module"] = "communecter";
                                        $split = explode("/", $value["urlImg"]);
                                        $paramsImg["name"] = $split[count($split)-1];
                                        unset($value["urlImg"]);
                                    }
                                    if(!empty($value["startDate"])){
                                        $startDate = DateTime::createFromFormat('Y-m-d H:i:s', $value["startDate"]);
                                        $value["startDate"] = $startDate->format('d/m/Y H:i');
                                    }
                                    if(!empty($value["endDate"])){
                                        $endDate = DateTime::createFromFormat('Y-m-d H:i:s', $value["endDate"]);
                                        $value["endDate"] = $endDate->format('d/m/Y H:i');
                                    }
                                    if(!empty($value["geo"])){
                                        if(gettype($value["geo"]["latitude"]) != "string" )
                                            $value["geo"]["latitude"] = strval($value["geo"]["latitude"]);
                                        if(gettype($value["geo"]["longitude"]) != "string" )
                                            $value["geo"]["longitude"] = strval($value["geo"]["longitude"]);
                                    }
                                    $value["collection"] = $typeElement ;
                                    $value["key"] = Element::getControlerByCollection($typeElement);
                                    $value["paramsImport"] = array( "link" => (empty($paramsLink)?null:$paramsLink),
                                                                    "img" => (empty($paramsImg)?null:$paramsImg ));
                                    $value["preferences"] = array(  "isOpenData"=>true, 
                                                                    "isOpenEdition"=>true);
                                    if($typeElement == Organization::COLLECTION)
                                        $value["role"] = "creator";
                                    if($typeElement == Event::COLLECTION && empty($value["organizerType"]))
                                        $value["organizerType"] = Event::NO_ORGANISER;
                                    
                                    if(!empty($value["organizerId"])){
                                        $eltSimple = Element::getElementSimpleById($value["organizerId"], @$value["organizerType"]);
                                        if(empty($eltSimple)){
                                            unset($value["organizerId"]);
                                            if(!empty($value["organizerType"])) 
                                                $value["organizerType"] = Event::NO_ORGANISER;
                                        }
                                    }
                                    $element = array();
                                    $res = Element::save($value);
                                    $element["name"] =  $value["name"];
                                    $element["info"] = $res["msg"];
                                    $element["type"] = $typeElement;
                                    if(!empty($res["id"])){
                                        $element["url"] = "/#page.type.".$typeElement.".id.".$res["id"] ;
                                        $element["id"] = $res["id"] ;
                                    }
                                    
                                } else {
                                    $element["name"] = $exist["element"]["name"];
                                    $element["info"] = "L'élément existes déjà";
                                    $element["url"] = "/#page.type.".$typeElement.".id.".(String)$exist["element"]["_id"] ;
                                    $element["type"] = $typeElement ;
                                    $element["id"] = (String)$exist["element"]["_id"] ;
                                }
                            }
                        }else{
                            $element["name"] = $exist["element"]["name"];
                            $element["info"] = "L'élément n'a pas d'adresse.";  
                        }
                    }
                }
                catch (CTKException $e){
                    $element["name"] =  $value["name"];
                    $element["info"] = $e->getMessage();
                }
                $resData[] = $element;     
            }
            $params = array("result" => true, 
                            "resData" => $resData);
        }
        else
        {
            $params = array("result" => false); 
        }
      
        return $params;
    }
    
    public static  function setCedex($post){        
        if($post['typeFile'] == "csv"){
            $file = $post['file'];
            //$headFile = $file[0];
            unset($file[0]);
        }else{
            $file = json_decode($post['file'][0], true);
        }
        $bon = "";
        $erreur = "";
        $nb = 0;
        foreach ($file as $keyFile => $valueFile){
            if( !empty($valueFile) && !empty($valueFile[1]) && strlen(trim($valueFile[1])) > 5 && isset($valueFile[9]) && isset($valueFile[10]) ){
                $newCP = array();
                $cp = substr(trim($valueFile[1]), 0,5);
                $cedex = substr(trim($valueFile[1]), 5);
                $lat = $valueFile[9];
                $lon = $valueFile[10];
                $where = array("postalCodes.postalCode" => $cp);
                $existes = PHDB::find(City::COLLECTION, $where);
                if(empty($existes)){
                    $city = SIG::getCityByLatLngGeoShape($lat, $lon,null);
                    if(!empty($city)){
                        $newCP["postalCode"] = $cp;
                        $newCP["complement"] = trim($cedex);
                        $newCP["name"] = mb_strtoupper(trim($valueFile[2])).$cedex;
                        $newCP["geo"] = array(   "@type"=>"GeoCoordinates", 
                                        "latitude" => $lat, 
                                        "longitude" => $lon);
                        $newCP["geoPosition"] = array(  "type"=>"Point", 
                                                        "coordinates" => array( floatval($lon), 
                                                                        floatval($lat)));
                        //var_dump($newCP);
                        $city["postalCodes"][] = $newCP;
                        
                        $res = PHDB::update( City::COLLECTION, 
                                array("_id"=>new MongoId((String)$city["_id"])),
                                array('$set' => array("postalCodes" => $city["postalCodes"])));
                        $nb++;
                        $bon .=  "<br> 'cp' : '".$cp."' , 'complement' : '".trim($cedex)."' , 'name' : '".trim($valueFile[2])."' ";
                    }else{
                        $erreur .=  "<br> 'error' : 'city not found' , cp' : '".$cp."' , 'complement' : '".trim($cedex)."' , 'name' : '".trim($valueFile[2])."' ";
                    }
                }else{
                    $erreur .=  "<br> 'error' : 'cp exist déjà' , cp' : '".$cp."' , 'complement' : '".trim($cedex)."' , 'name' : '".trim($valueFile[2])."' ";
                } 
            }
        }
        echo "Il y a ".$nb."update" ;
        echo "<br><br>---------------------<br><br>";
        echo $erreur ;
        echo "<br><br>---------------------<br><br>";
        echo $bon ;
    } 
    public static  function setWikiDataID($post){        
        if($post['typeFile'] == "csv"){
            $file = $post['file'];
            //$headFile = $file[0];
            unset($file[0]);
        }
        $bon = "";
        $erreur = "";
        $nb = 0;
        $elements = array();
        $elementsWarnings = array();
        foreach ($file as $keyFile => $valueFile){
            if( !empty($valueFile) && !empty($valueFile[0]) && isset($valueFile[2]) ){
                
                $insee = $valueFile[2];
                $split = explode("http://www.wikidata.org/entity/", $valueFile[0]) ;
                $wikidataID = $split[count($split)-1];
                
                $where = array("insee" => $insee);
                $city = PHDB::find(City::COLLECTION, $where);
                if(!empty($city)){
                    foreach ($city as $key => $value) {
                        $value["modifiedByBatch"][] = array("setWikiDataID" => new MongoDate(time()));
                        $res = PHDB::update( City::COLLECTION, 
                                    array("_id"=>new MongoId($key)),
                                    array('$set' => array(  "wikidataID" => $wikidataID,
                                                            "modifiedByBatch" => $value["modifiedByBatch"])));
                        $good = array();
                        $good["insee"] = $insee ;
                        $good["wikidataID"] = $wikidataID ;
                        $elements[] =  $good;
                    }
                    
                }else{
                    $error = array();
                    $error["insee"] = $insee ;
                    $error["wikidataID"] = $wikidataID ;
                    $elementsWarnings[] =  $error;
                } 
            }
        }
        $params = array("result"=>true,
                            "elements"=>json_encode($elements),
                            "elementsWarnings"=>json_encode($elementsWarnings),
                            "listEntite"=>array());
        return $params ;
    }
    public static function isUncomplete($idEntity, $typeEntity){
        $res = false ;
        $entity = PHDB::findOne($typeEntity,array("_id"=>new MongoId($idEntity)));
        
        if(!empty($entity["warnings"]) || (!empty($entity["state"]) && $entity["state"] == "uncomplete"))
            $res = true;
        return $res ;
    }
    public static function checkWarning($idEntity, $typeEntity ,$userId){
        $entity = PHDB::findOne($typeEntity,array("_id"=>new MongoId($idEntity)));
        unset($entity["warnings"]);
        if($typeEntity == Project::COLLECTION){
            $newEntity = Project::getAndCheckProjectFromImportData($entity, $userId, null, true, true);
            if(!empty($newEntity["warnings"]))
                Project::updateProjectField($idEntity, "warnings", $newEntity["warnings"], $userId );
            else
                Project::updateProjectField($idEntity, "state", true, $userId ); 
        }
        if($typeEntity == Organization::COLLECTION){
            $newEntity = Organization::getAndCheckOrganizationFromImportData($entity, null, true, true);
            if(!empty($newEntity["warnings"])){
                Organization::updateOrganizationField($idEntity, "warnings", $newEntity["warnings"], $userId );
            }
            else{
                Organization::updateOrganizationField($idEntity, "state", true, $userId );
                Organization::updateOrganizationField($idEntity, "warnings", array(), $userId ); 
            }
                
        }
        if($typeEntity == Event::COLLECTION){
            $newEntity = Event::getAndCheckOrganizationFromImportData($entity, $userId, null, true, true);
            if(!empty($newEntity["warnings"]))
                Event::updateOrganizationField($idEntity, "warnings", $newEntity["warnings"], $userId );
            else
                Event::updateOrganizationField($idEntity, "state", true, $userId ); 
        }
    }
    public static function getParams($file, $type, $url) {
        $param = array();
        if ($type == Organization::COLLECTION) {
            $map = TranslateGeoJsonToPh::$mapping_organisation;
        } elseif ($type == Person::COLLECTION) {
            $map = TranslateGeoJsonToPh::$mapping_person;
        } elseif ($type == Event::COLLECTION) {
            $map = TranslateGeoJsonToPh::$mapping_event;
        } elseif ($type == Project::COLLECTION) {
            $map = TranslateGeoJsonToPh::$mapping_project;
        } 
        $param['typeElement'] = $map["type_elt"];
        foreach ($map as $key => $value) {
            $param['infoCreateData'][$key]["valueAttributeElt"] = $value;
            $param['infoCreateData'][$key]["idHeadCSV"] = $key;
        }
        $param['typeFile'] = 'json';
        $param['pathObject'] = 'features';
        $param['nameFile'] = 'geojson';
        $param['key'] = 'geojson';
        $param['warnings'] = false;
        $param['nbTest'] = "5";
        $url_length = strlen($url);
        if ((isset($url)) && 
            (((substr($url, 0, 35) == "http://umap.openstreetmap.fr/en/map")) || ((substr($url, 0, 35) == "http://umap.openstreetmap.fr/fr/map"))) &&
            (((substr($url, $url_length - 8, $url_length)) == "geojson/") || ((substr($url, $url_length - 7, $url_length)) == "geojson"))
            ) {
            $res = self::getUmapResult($url, $param);
        } elseif ((isset($url)) && 
            (((substr($url, 0, 35) == "http://umap.openstreetmap.fr/en/map")) || (substr($url, 0, 35) == "http://umap.openstreetmap.fr/fr/map"))
            ) {
            $pos_underscore = strpos($url, "_");
            $id_map = (substr($url, $pos_underscore + 1, strlen($url)))."/";
            $url = "http://umap.openstreetmap.fr/fr/map/".$id_map."geojson";
            $res = self::getUmapResult($url, $param);
        } elseif ((isset($url)) && 
            ((substr($url, 0, 21) == "http://u.osmfr.org/m/"))
            ) {
            $id_map = (substr($url, 21, strlen($url))); 
            $url = "http://umap.openstreetmap.fr/fr/map/".$id_map."geojson";
            $res = self::getUmapResult($url, $param);
        } elseif ((isset($file)) || (isset($url))) {
            $param['file'][0] = (isset($file)) ? $file : file_get_contents($url);
           
            $result = self::previewData($param);
            $res = json_decode($result['elements']);
        } 
        if (isset($res)) {
            return $res;
        } 
    }
    public static function getDatalayersUmap($url){
        $url_map = $url;
        $umap_data = file_get_contents($url_map);
        $umap_data = json_decode($umap_data, true);
        $list_id = array();
        $list_url_datalayers = array();
        foreach ($umap_data["properties"]["datalayers"] as $key => $value) {
            $url_datalayers = 'http://umap.openstreetmap.fr/en/datalayer/'.$value['id'];
            array_push($list_url_datalayers, $url_datalayers);
        }
        return $list_url_datalayers;
    }
    public static function getUmapResult($url, $param) {
        $umap_data = file_get_contents($url);
        $list_url_data = self::getDatalayersUmap($url);
        $param['nameFile'] = $url;
        $res = array();
        foreach ($list_url_data as $keyDatalayer => $valueDatalayer) {  
            $datalayers_data = file_get_contents($valueDatalayer);
            $param['file'][0] = $datalayers_data;
            $result = self::previewData($param);
            $result = $result['elements'];
            if (!empty(json_decode($result))) {
                array_push($res, json_decode($result));
            }
        }
        return $res;
    }
    public static function getStandartMapping($allMappings) {
        $orga_standart_mapping = PHDB::find(self::MAPPINGS, array("key" => "organizationStandart"));
        $person_standart_mapping = PHDB::find(self::MAPPINGS, array("key" => "personStandart"));
        $event_standart_mapping = PHDB::find(self::MAPPINGS, array("key" => "eventStandart"));
        $projet_standart_mapping = PHDB::find(self::MAPPINGS, array("key" => "projetStandart"));
        foreach ($orga_standart_mapping as $key => $value) {
            $orga_key_standart = $key;
        }
        foreach ($person_standart_mapping as $key => $value) {
            $person_key_standart = $key;
        }
        foreach ($event_standart_mapping as $key => $value) {
            $event_key_standart = $key;
        }
        foreach ($projet_standart_mapping as $key => $value) {
            $projet_key_standart = $key;
        }
        foreach ($orga_standart_mapping as $key => $value) {
            $allMappings[$orga_key_standart] = $value;
        }
        foreach ($person_standart_mapping as $key => $value) {
            $allMappings[$person_key_standart] = $value;
        }
        foreach ($event_standart_mapping as $key => $value) {
            $allMappings[$event_key_standart] = $value;
        }
        foreach ($projet_standart_mapping as $key => $value) {
            $allMappings[$projet_key_standart] = $value;
        }
        return $allMappings;
    }

        //SetMapping : Ajout & Update d'un mapping
    public static function setMappings($userid,$post)
    {
        //On stock les variables
        $params = array("result" => false);
        $name = @$post['name'];
        $attributeElt = @$post['attributeElt'];
        $attributeSource = @$post['attributeSource'];
        $key = trim(@$name);
        $typeElement = @$post['typeElement'];
        $idMapping = @$post['idMapping'];

        //On les stock dans un objets.
        for($i = 0; $i<count($attributeSource); $i++)
        {
            $fields[$attributeSource[$i]] = $attributeElt[$i];
        }

        $mapping = array(
            "key" => $key,
            "name" => $name,
            "typeElement" => $typeElement,
            "userId" => $userid,
            "fields" => $fields);

        //Dans le cas si c'est un ajout
        if($idMapping == "-1" ||  $idMapping  == "5b0d1b379eaf44ea598b4580" ||$idMapping == "5b0d1b379eaf44ea598b4581" || $idMapping ==  "5b0d1b379eaf44ea598b4582" || $idMapping ==  "5b0d1b379eaf44ea598b4583"|| $idMapping == "5b1654d39eaf4427171cd718") 
        {
            $request = PHDB::insert(self::MAPPINGS, $mapping);
            $find = PHDB::findOne(self::MAPPINGS, array("key" => $key, "name" => $name, "userId" => $userid, "typeElement" => $typeElement));

            $params = array("result" => true,
                    "name" => $name,
                    "key" => $key,
                    "typeElement" => $typeElement,
                    "attributeElt" => $attributeElt,
                    "attributeSource" => $attributeSource,
                    "_id" => (String)$find['_id']);
        }

        //Dans le cas si c'est un upload.
        else if($idMapping != "")
        {

       $request = PHDB::update(self::MAPPINGS,array("_id"=>new MongoId((String)$idMapping)), $mapping);
       $params = array("result" => true,
               "idMapping" => $idMapping,
               "name" => $name,
               "key" => $key,
               "typeElement" => $typeElement,
               "attributeElt" => $attributeElt,
               "attributeSource" => $attributeSource);
        }

        return $params;
    }

    //Suppression d'un mapping
   public static function deleteMapping($post){
       $params = array("result" => false);
       $request = PHDB::remove(self::MAPPINGS,array("_id"=>new MongoId((String)$post['idMapping'])));
       return $params = array("result" => true,
                        "idMappingdelete" => $post['idMapping']);
   }
}
