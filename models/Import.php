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
        else if($_POST['typeFile'] == "csv")
            $params = self::parsingCSV($post); 
        return $params ;
    }

    public static function parsingCSV($post) {
        
        $attributesElt = self::getAllPathJson(file_get_contents("../../modules/communecter/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH));
        if($post['idMapping'] != "-1"){
            $where = array("_id" => new MongoId($post['idMapping']));
            $fields = array("fields");
            $mapping = self::getMappings($where, $fields);
            $arrayMapping = $mapping[$post['idMapping']]["fields"];
        }
        else
            $arrayMapping = array();

        $params = array("result"=>true,
                        "attributesElt"=>$attributesElt,
                        "arrayMapping"=>$arrayMapping,
                        "typeFile"=>$post['typeFile']);
        return $params ;
    }

    public static function parsingJSON($post){
        header('Content-Type: text/html; charset=UTF-8');
        $params = array("result"=>false);
        if(isset($post['file'])) {
            
            $json = $post['file'][0];
            //(empty($post["path"]) ? $post['file'][0] : $post['file'][0][$post["path"]]);

            $csv->setCsvControl($post['separateurDonnees'], $post['separateurTexte'], '"');
            $arrayNameFile = explode(".", $file['fileImport']['name']);
            // On découpe le fichier s'il est trop gros, 5000 ligne par fichier

            $path = sys_get_temp_dir().'/filesImportData/' ;
            if(!file_exists($path))
                mkdir($path , 0775);

            $path = sys_get_temp_dir().'/filesImportData/'.$arrayNameFile[0].'/';
            if(file_exists($path))
               rmdir($path);
            mkdir($path , 0775);

            $countLine = 0;
            $countFile = 1;

            foreach ($csv as $key => $value) 
            {

                //if(is_string($value))

                $arrayCSV[$key] = $value;
                if($key == 0)
                    $headerCSV = $value;
                
                if($countLine == 0 || $key == 0)
                    $arrayCSV[0] = $headerCSV;
                else
                    $arrayCSV[$countLine] = $value;


                if($countLine == 5000)
                {
                    $nameFile = $arrayNameFile[0].'_'.$countFile.'.csv' ;
                    Import::createCSV($arrayCSV, $nameFile, $path);
                    $countLine = 0;
                    $countFile++;
                    $arrayCSV = array();
                }
                else
                    $countLine++;

            }
            $nameFile = $arrayNameFile[0].'_'.$countFile.'.csv' ;
            Import::createCSV($arrayCSV, $nameFile, $path);

            $params = array("createLink"=>true,
                            "typeFile" => "csv",
                            "arrayCSV" => $arrayCSV,
                            "nameFile"=>$file['fileImport']['name'],
                            "idCollection"=>$post['chooseCollection']);
        }
        else
        {
            $params = array("createLink"=>false);  
        }

        return $params ;
    }*/

    
    

    public static  function previewDataCSV($post) 
    {
        /**** new ****/
        $arrayJson = array();
        $notGeo = false ;

        if(isset($post['infoCreateData']) && isset($post['idCollection']) && isset($post['file']) && isset($post['nameFile'])){

            $paramsInfoCollection = array("_id"=>new MongoId($post['idCollection']));
            $infoCollection = Import::getMicroFormats($paramsInfoCollection);
            $arrayCSV = $post['file'];
            $arrayHeadCSV = $arrayCSV[0];

            $i = 0 ;
            foreach ($arrayCSV as $keyCSV => $lineCSV){
                $jsonData = array();
                if($i>0 && $lineCSV[0] != null){
                    
                    if (($i%500) == 0)
                        set_time_limit(30) ;
                    
                    foreach($post['infoCreateData']as $key => $objetInfoData){
                       
                        $valueData = false ;
                        if(!empty($lineCSV[$objetInfoData['idHeadCSV']]))
                            $valueData = $lineCSV[$objetInfoData['idHeadCSV']] ;
                        
                        if(!empty($valueData)) {
                            $mappingTypeData = explode(".", $post['idCollection'].".mappingFields.".$objetInfoData['valueLinkCollection']);
                            $typeData = ArrayHelper::getValueJson($infoCollection,$mappingTypeData);
                            $mapping = explode(".", $objetInfoData['valueLinkCollection']);
                           
                            if(isset($jsonData[$mapping[0]])){
                                if(count($mapping) > 1){ 
                                    $newmap = array_splice($mapping, 1);
                                    $jsonData[$mapping[0]] = FileHelper::create_json_with_father($newmap, $valueData, $jsonData[$mapping[0]], $typeData);
                                }
                                else{
                                    $jsonData[$mapping[0]] = $valueData;
                                }
                            }
                            else{
                                if(count($mapping) > 1){ 
                                    $newmap = array_splice($mapping, 1);
                                    $jsonData[$mapping[0]] = FileHelper::create_json($newmap, $valueData, $typeData);
                                }
                                else{
                                    $jsonData[$mapping[0]] = $valueData;
                                }
                            }
                            
                        }
                        
                    }
                    if(empty($post['key']))
                        $keyEntity = null;
                    else
                        $keyEntity = $post['key'];

                    if(empty($post['warnings']))
                        $warnings = null;
                    else
                        $warnings = true;
                    
                    $entite = Import::checkData($infoCollection[$post['idCollection']]["key"], $jsonData, $post);

                    if(empty($entite["geo"]) && !empty($entite["msgError"]))
                        $notGeo = true ;
                    
                    if(empty($entite["msgError"]))
                        $arrayJson[] = $entite ;
                    else
                        $arrayJsonError[] = $entite ; 
                    
                }
                $i++;
            }

            if(!isset($arrayJson))
                $arrayJson = array();

            if(!isset($arrayJsonError))
                $arrayJsonError = array();

            $listEntite = $arrayJson;
            foreach ($arrayJsonError as $key => $value) 
            {
                $listEntite[] = $value;
            }
            $listEntite = Import::createArrayList($listEntite) ;

            $params = array("result" => true,
                            "jsonImport"=> json_encode($arrayJson),
                            "jsonError"=> json_encode($arrayJsonError),
                            "listEntite" => $listEntite,
                            "geo"=>$notGeo);
        }
        else
        {
            $params = array("result" => false); 
        }
        return $params;
    }


    public static function checkData($keyCollection, $data, $post){
        $res = array() ;

        if(!empty($post['key']))
            $data["source"]['key'] = $post['key'];

        $data["type"] = "Groupe gouvernemental";

        if(!empty($post["warnings"]) && $post["warnings"] == "true")
            $warnings = true ;
        else
            $warnings = false ;
        

        if($keyCollection == "Organizations"){
            try{    
                $newOrganization = Organization::newOrganizationFromImportData($data, $post["creatorEmail"], $warnings);
                $newOrganization["role"] = $post["role"];
                $newOrganization["creator"] = $post["creatorID"];
                $newOrganization2 = Organization::getQuestionAnwser($newOrganization);
                $res = Organization::getAndCheckOrganizationFromImportData($newOrganization2, null, null, $warnings) ;
            }
            catch (CTKException $e){
                if(empty($newOrganization))
                    $newOrganization = $data;
                $newOrganization["msgError"] = $e->getMessage();
                $res = $newOrganization ;
            }
        } else if($keyCollection == "Projets"){
            try{

                $newProject = Project::createProjectFromImportData($data);
                $newProject2 = Project::getQuestionAnwser($newProject);
                $res = Project::getAndCheckProjectFromImportData($newProject2, $post["creatorID"], null, null, $warnings);
            }
            catch(CTKException $e){
                if(empty($newProject))
                    $newProject = $data;

                $newProject["msgError"] = $e->getMessage();
                
                $res = $newProject ;
            }
        } else if($keyCollection == "Person"){
            try{
                $invite = false ;
                if(!empty($post["invite"])){
                    $invite = true ;
                    $data["nameInvitor"] = $post["nameInvitor"];
                    $data["msgInvite"] = $post["msgInvite"];
                }

                $newPerson = Person::createPersonFromImportData($data, true);
                $res = Person::getAndCheckPersonFromImportData($newPerson, $invite, null, null, $post["warnings"]);
                
                
            }
            catch(CTKException $e){
                if(empty($newPerson))
                    $newPerson = $data;

                $newPerson["msgError"] = $e->getMessage();
                
                $res = $newPerson ;
            }
        }else if($keyCollection == "Events"){
            try{
                //
                $newEvent = Event::newEventFromImportData($data, $post["creatorEmail"], $warnings);
                $newEvent["creator"] = $post["creatorID"];

                $res = Event::getAndCheckEventFromImportData($newEvent, null, null, $warnings) ;
            }
            catch (CTKException $e){
                if(empty($newEvent))
                    $newEvent = $data;
                $newEvent["msgError"] = $e->getMessage();
                $res = $newEvent ;
            }
        }

        return $res ;
    }

    public static function previewDataJSON($post) 
    {
        
        $params = array("result" => false); 
        $notGeo = false ;
        if(isset($post['infoCreateData']) && isset($post['file']))
        {

            $paramsInfoCollection = array("_id"=>new MongoId($post['idCollection']));
            $infoCollection = Import::getMicroFormats($paramsInfoCollection);
            $jsonFile = json_decode($post['file'][0], true);
            //var_dump($jsonFile);

            if(!empty($post["pathObject"])){
                $obj = json_decode($post['file'][0], true);
                $map = explode(".", $post["pathObject"]) ;
                $jsonFile2 = ArrayHelper::getValueJson($obj, $map);
            }else{
                if(substr($post['file'][0], 0,1) == "{")
                    $jsonFile2[] = $jsonFile ;
                else
                    $jsonFile2 = $jsonFile ; 
            }

            if(!empty($post['nbTest']))
                $nb = 0 ;

            foreach ($jsonFile2 as $keyJSON => $valueJSON){
                //var_dump("--------------------------------------");
                $jsonData = array();
                foreach($post['infoCreateData']as $key => $objetInfoData){
                    
                    $cheminLien = explode(".", $objetInfoData['idHeadCSV']);
                    //var_dump($objetInfoData['idHeadCSV']);
                    
                    $valueData = ArrayHelper::getValueJson($valueJSON, $cheminLien);
                    //var_dump($valueData);
                    if(!empty($valueData) && isset($valueData)){
                        //var_dump("Here");
                        $mappingTypeData = explode(".", $post['idCollection'].".mappingFields.".$objetInfoData['valueLinkCollection']);
                        $typeData = ArrayHelper::getValueJson($infoCollection,$mappingTypeData);
                        
                        $mapping = explode(".", $objetInfoData['valueLinkCollection']);
                       
                        if(isset($jsonData[$mapping[0]])){
                            if(count($mapping) > 1)
                            { 
                                $newmap = array_splice($mapping, 1);
                                $jsonData[$mapping[0]] = FileHelper::create_json_with_father($newmap, $valueData, $jsonData[$mapping[0]], $typeData);
                            }
                            else
                            {
                                $jsonData[$mapping[0]] = $valueData;
                            }
                        } else {
                            if(count($mapping) > 1) { 
                                $newmap = array_splice($mapping, 1);
                                $jsonData[$mapping[0]] = FileHelper::create_json($newmap, $valueData, $typeData);
                            }
                            else
                            {
                                $jsonData[$mapping[0]] = $valueData;
                            }
                        }
                    }

                }
                if(empty($jsonData))
                    $jsonData = array();
                
                if(empty($post['key']))
                    $keyEntity = null;
                else
                    $keyEntity = $post['key'];

                

                $entite = Import::checkData($infoCollection[$post['idCollection']]["key"], $jsonData, $post);

                if(empty($entite["geo"]) && !empty($entite["msgError"]))
                    $notGeo = true ;

                if(empty($entite["msgError"]))
                    $arrayJson[] = $entite ;
                else
                    $arrayJsonError[] = $entite ; 


                if(!empty($post['nbTest'])){
                    $nb++;
                    if($nb >= $post['nbTest'])
                        break;
                }
                   

            }

            if(!isset($arrayJson))
                $arrayJson = array();

            if(!isset($arrayJsonError))
                $arrayJsonError = array();

            $listEntite = $arrayJson;
            foreach ($arrayJsonError as $key => $value) 
            {
                $listEntite[] = $value;
            }
            
            if(!empty($listEntite))
                $listEntite = Import::createArrayList($listEntite) ;

            
            if($post['idMapping'] != "-1"){
                $where = array("_id" => new MongoId($post['idMapping']));
                $fields = array("fields");
                $mapping = self::getMappings($where, $fields);
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
                $arbre = self::getAllPathJson($json); 
            else{
                $arbre = array();
                foreach (json_decode($json,true) as $key => $value) {
                    $arbre = self::getAllPathJson(json_encode($value), $arbre); 
                }
            }
            $attributesElt = self::getAllPathJson(file_get_contents("../../modules/communecter/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH));
            $params = array("result"=>true,
                        "attributesElt"=>$attributesElt,
                        "arrayMapping"=>$arrayMapping,
                        "arbre"=>$arbre,
                        "typeFile"=>$post['typeFile']);          
        }
        return $params ;
    }

    public static function getMappings($where=array(),$fields=null){
        $allMapping = PHDB::find(self::MAPPINGS, $where, $fields);
        return $allMapping;
    }

    public static function getAllPathJson($json, $attributesElt=null){
        $arrayJson = json_decode($json, true);
        if($attributesElt==null)
            $attributesElt = array() ;
        $arrayPathMapping = explode(";", ArrayHelper::getAllPath($arrayJson));
        foreach ($arrayPathMapping as $keyPathMapping => $valuePathMapping){
            if(!empty($valuePathMapping) && !in_array($valuePathMapping, $attributesElt))
                $attributesElt[] =  $valuePathMapping;
        }
        return $attributesElt ;
    }

    public static  function previewData($post){
        $params = array("result"=>false);
        $elements = array();
        $nb = 0 ;
        $elementsWarnings = array();
        if(!empty($post['infoCreateData']) && !empty($post['file']) && !empty($post['nameFile']) && !empty($post['typeFile'])){
            $mapping = json_decode(file_get_contents("../../modules/communecter/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH), true);
            $attributesElt = self::getAllPathJson(file_get_contents("../../modules/communecter/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH));
            if($post['typeFile'] == "csv"){
                $file = $post['file'];
                $headFile = $file[0];
                unset($file[0]);
            }else{
                $file = json_decode($post['file'][0], true);
            }
            
            foreach ($file as $keyFile => $valueFile){
                $nb++;
                //if(!empty($valueFile)){
                    $element = array();
                    foreach ($post['infoCreateData'] as $key => $value) {
                        $valueData = null;
                        if($post['typeFile'] == "csv" && in_array($value["idHeadCSV"], $headFile)){
                            $idValueFile = array_search($value["idHeadCSV"], $headFile);
                            $valFile =  (!empty($valueFile[$idValueFile])?$valueFile[$idValueFile]:null);
                        }else{
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

                    $element = self::checkElement($element, $post['typeElement']);

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
           // var_dump($nb);
            $params = array("result"=>true,
                            "elements"=>json_encode(json_decode(json_encode($elements),true)),
                            "elementsWarnings"=>json_encode(json_decode(json_encode($elementsWarnings),true)),
                            "listEntite"=>self::createArrayList(array_merge($elements, $elementsWarnings)));
        }
        return $params;
    }  

    public static function createArrayList($list) {
        $head = array("name", "warnings", "msgError") ;
        $tableau = array($head);
        foreach ($list as $keyList => $valueList){
            $ligne = array();
            $ligne[] = (empty($valueList["name"])? "" : $valueList["name"]);
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
            $msg .= Yii::t("import",$codeWarning, null, Yii::app()->controller->module->id);
        }
        return $msg;
    }

    public static function checkElement($element, $typeElement){
        $result = array("result" => true);
        
        if($typeElement != Person::COLLECTION){
            $address = (empty($element['address']) ? null : $element['address']);
            $geo = (empty($element['geo']) ? null : $element['geo']);
            $detailsLocality = self::getAndCheckAddressForEntity($address, $geo) ;
            if($detailsLocality["result"] == true){
               $element["address"] = $detailsLocality["address"] ;
               $element["geo"] = $detailsLocality["geo"] ;
               $element["geoPosition"] = $detailsLocality["geoPosition"] ; 
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
        
        $element = self::getWarnings($element, $typeElement, true) ;
        $resDataValidator = DataValidator::validate(Element::getControlerByCollection($typeElement), $element, true);
        if($resDataValidator["result"] != true){
            //$element["msgError"] = ((empty($resDataValidator["msg"]->getMessage()))?$resDataValidator["msg"]:$resDataValidator["msg"]->getMessage());
            $element["msgError"] = $resDataValidator["msg"];
        }
        
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


    public static function getAndCheckAddressForEntity($address = null, $geo = null){
        
        $result = array("result" => false);
        $newAddress = array(    '@type' => 'PostalAddress',
                                 'streetAddress' =>  (empty($address["streetAddress"])?'':$address["streetAddress"]), 
                                 'postalCode' =>  (empty($address["postalCode"])?'':$address["postalCode"]),
                                 'addressLocality' =>  (empty($address["addressLocality"])?'':$address["addressLocality"]),
                                 'addressCountry' =>  (empty($address["addressCountry"])?'':$address["addressCountry"]),
                                 'codeInsee' =>  '');

        $newGeo["geo"] = array(  "@type"=>"GeoCoordinates",
                        "latitude" => (empty($geo["latitude"])?'':$address["latitude"]),
                        "longitude" => (empty($geo["longitude"])?'':$address["longitude"]));

        $street = (empty($address["streetAddress"])?null:$address["streetAddress"]);
        $cp = (empty($address["postalCode"])?null:$address["postalCode"]);
        $nameCity = (empty($address["addressLocality"])?null:$address["addressLocality"]);
        $country = (empty($address["addressCountry"])?null:$address["addressCountry"]);
        $lat = (empty($geo["latitude"])?null:$geo["latitude"]);
        $lon = (empty($geo["longitude"])?null:$geo["longitude"]);

        //Cas 1 On a que l'addresse
        if(!empty($address) && empty($geo)){

            $resCedex = City::getCityByCedex($cp);
            if(!empty($resCedex)){
                $newGeo["geo"] = $resCedex["geo"];
                $newAddress["codeInsee"] = $resCedex["insee"];
                $newAddress['addressCountry'] = $resCedex["country"];
                $newAddress['addressLocality'] = $resCedex["name"];
                $newAddress['postalCode'] = $resCedex["cp"];
                $newAddress['regionName'] = $resCedex["regionName"];
                $newAddress['depName'] = $resCedex["depName"];
                $cedex = true;

            }else{
                $resultDataGouv = ( ( !empty($address["addressCountry"]) && $address["addressCountry"] == "FR" ) ? ( empty($cp)?null:json_decode(SIG::getGeoByAddressDataGouv($street, $cp, $nameCity), true) ) : null ) ;
                if(!empty($resultDataGouv["features"])){
                    $newGeo["geo"]["latitude"] = strval($resultDataGouv["features"][0]["geometry"]["coordinates"][1]);
                    $newGeo["geo"]["longitude"] = strval($resultDataGouv["features"][0]["geometry"]["coordinates"][0]);
                }else{
                    $resultNominatim = json_decode(SIG::getGeoByAddressNominatim($street, $cp, $nameCity, $country), true);
                    if(!empty($resultNominatim[0])){
                        $newGeo["geo"]["latitude"] = $resultNominatim[0]["lat"];
                        $newGeo["geo"]["longitude"] = $resultNominatim[0]["lon"];
                    }else{
                        $resultGoogle = json_decode(SIG::getGeoByAddressGoogleMap($street, $cp, $nameCity, $country), true);
                        $resG = false ;
                        if(!empty($resultGoogle["results"][0]["address_components"])){
                            foreach ($resultGoogle["results"][0]["address_components"] as $key => $value) {
                                if(in_array("locality", $value["types"]))
                                    $resG = true ;
                            }
                        }
                        
                        //var_dump($resG);
                        if(!empty($resultGoogle["results"]) && $resG == true){
                            $newGeo["geo"]["latitude"] = strval($resultGoogle["results"][0]["geometry"]["location"]["lat"]);
                            $newGeo["geo"]["longitude"] = strval($resultGoogle["results"][0]["geometry"]["location"]["lng"]);
                        }else{
                            $resultDataGouv = ( ( !empty($address["addressCountry"]) && $address["addressCountry"] == "FR" ) ? ( empty($cp)?null:json_decode(SIG::getGeoByAddressDataGouv(null, $cp, $nameCity), true) ) : null ) ;
                            if(!empty($resultDataGouv["features"])){
                                $newGeo["geo"]["latitude"] = strval($resultDataGouv["features"][0]["geometry"]["coordinates"][1]);
                                $newGeo["geo"]["longitude"] = strval($resultDataGouv["features"][0]["geometry"]["coordinates"][0]);
                            }else{
                                $resultNominatim = json_decode(SIG::getGeoByAddressNominatim(null, $cp, $nameCity, $country), true);
                                if(!empty($resultNominatim[0])){
                                    $newGeo["geo"]["latitude"] = $resultNominatim[0]["lat"];
                                    $newGeo["geo"]["longitude"] = $resultNominatim[0]["lon"];
                                }else{
                                    $resultGoogle = json_decode(SIG::getGeoByAddressGoogleMap(null,$cp, $nameCity, $country), true);
                                    $resG = false ;
                                    if(!empty($resultGoogle["results"][0]["address_components"])){
                                        foreach ($resultGoogle["results"][0]["address_components"] as $key => $value) {
                                            if(in_array("locality", $value["types"]))
                                                $resG = true ;
                                        }
                                    }

                                    if(!empty($resultGoogle["results"]) && $resG == true){
                                        $newGeo["geo"]["latitude"] = strval($resultGoogle["results"][0]["geometry"]["location"]["lat"]);
                                        $newGeo["geo"]["longitude"] = strval($resultGoogle["results"][0]["geometry"]["location"]["lng"]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } // Cas 2 il n'y a que la Géo 
        else if(empty($address) && !empty($geo)){
            if(!empty($geo["latitude"]) && !empty($geo["longitude"])){
                $newGeo["geo"]["latitude"] = $geo["latitude"] ;
                $newGeo["geo"]["longitude"] =  $geo["longitude"] ;
                $resultNominatim = json_decode(SIG::getLocalityByLatLonNominatim($geo["latitude"], $geo["longitude"]), true);
                if(!empty($resultNominatim["address"]["postcode"])){
                    $arrayCP = explode(";", $resultNominatim["address"]["postcode"]);
                    $cp = $arrayCP[0];
                    $newAddress['postalCode'] = $arrayCP[0];
                }
            }
        }

        if(!empty($newGeo["geo"]["latitude"]) && !empty($newGeo["geo"]["longitude"])){
            if(empty($cedex)){
                $city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"],$cp);
                if(empty($city)){
                    $cities = SIG::getCityByLatLng($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"], $cp);
                    $city = (!empty($cities[0])?$cities[0]:null);
                }
                if(empty($city))
                    $city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"],null);
                if(!empty($city)){
                    $newAddress["codeInsee"] = $city["insee"];
                    $newAddress['addressCountry'] = $city["country"];
                    $newAddress['regionName'] = (@$city["regionName"]?$city["regionName"]:"");
                    $newAddress['depName'] = (@$city["depName"]?$city["depName"]:"");
                    foreach ($city["postalCodes"] as $keyCp => $valueCp){
                        if(empty($cp)){
                            if($valueCp["name"] == $city["alternateName"]){
                                $newAddress['addressLocality'] = $valueCp["name"];
                                $newAddress['postalCode'] = $valueCp["postalCode"];
                            }
                        }
                        else if($valueCp["postalCode"] == $cp){
                            $newAddress['addressLocality'] = $valueCp["name"];
                        }
                    }
                }
            }
            
            $newGeo["geoPosition"] = array("type"=>"Point",
                                                "coordinates" =>
                                                    array(
                                                        floatval($newGeo["geo"]['longitude']),
                                                        floatval($newGeo["geo"]['latitude'])));
            $result["result"] = true;
            $result["geoPosition"] = $newGeo["geoPosition"];
        }
        $result["geo"] = $newGeo["geo"];
        $result["address"] = $newAddress;
        return $result;
    }

    public static function getWarnings($element, $typeElement, $import = null){
        $warnings = array();

        if(empty($element['name']))
            $warnings[] = "201" ;

        if(empty($element['email']) && $typeElement == Person::COLLECTION)
            $warnings[] = "203" ;

        if($typeElement != Person::COLLECTION){
            if(!empty($element['address'])){
                if(empty($element['address']['postalCode'])) $warnings[] = "101" ;
                if(empty($element['address']['codeInsee'])) $warnings[] = "102" ;     
                if(empty($element['address']['addressCountry']))$warnings[] = "104" ;
                if(empty($element['address']['addressLocality'])) $warnings[] = "105" ;
            }else{
                $warnings[] = (empty($import)?"100":"110");
            }

            if(!empty($element['geo'])){
                if(empty($element['geo']['latitude'])) $warnings[] = "151" ;
                if(empty($element['geo']['longitude'])) $warnings[] = "152" ;
            }else {
                $warnings[] = (empty($import)?"150":"154") ;
            }
        }
        

        if(!empty($warnings))
            $element["warnings"] = $warnings;

        return $element;
    }

    public static function initMappings(){
        $mappings = json_decode(file_get_contents("../../modules/communecter/data/import/mappings.json", FILE_USE_INCLUDE_PATH), true);
        foreach ($mappings as $key => $value) {
            PHDB::insert( Import::MAPPINGS, $value );
        }
    }

     public static function addDataInDb($post, $moduleId = null)
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
                    if(@$value["address"]){
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
                                $paramsImg["module"] = $moduleId;
                                $split = explode("/", $value["urlImg"]);
                                $paramsImg["name"] =$split[count($split)-1];

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
                                $element["url"] = "/#".Element::getControlerByCollection($typeElement).".detail.id.".$res["id"] ;
                                $element["id"] = $res["id"] ;
                            }
                            
                        }else{
                            $element["name"] = $exist["element"]["name"];
                            $element["info"] = "L'élément existes déjà";
                            $element["url"] = "/#".Element::getControlerByCollection($typeElement).".detail.id.".(String)$exist["element"]["_id"] ;
                            $element["type"] = $typeElement ;
                            $element["id"] = (String)$exist["element"]["_id"] ;
                        }
                        
                    }else{
                        $element["name"] = $exist["element"]["name"];
                        $element["info"] = "L'élément n'a pas d'adresse.";  
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
<<<<<<< Updated upstream
                        $newCP["complement"] = trim($cedex);
                        $newCP["name"] = mb_strtoupper(trim($valueFile[2])).$cedex;
                        $newCP["geo"] = array(   "@type"=>"GeoCoordinates", 
                                        "latitude" => $lat, 
                                        "longitude" => $lon);
                        $newCP["geoPosition"] = array(   "type"=>"Point", 
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
=======
                        $newCP["complementPC"] = $complement;
                        $newCP["name"] = $lineCSV[2];
                        $newCP["geo"]["@type"] = "GeoCoordinates";
                        $newCP["geo"]["latitude"] = $lat;
                        $newCP["geo"]["longitude"] = $lon;
                        $newCP["geoPosition"] = array("type"=>"Point",
                                                        "float"=>"true",
                                                                    "coordinates" =>
                                                                        array(
                                                                            floatval($lon),
                                                                            floatval($lat)));
                        $city["postalCodes"][] = $newCP;
                        $new["city"] = $city["name"];
                        $new["name"] = $lineCSV[2];
                        PHDB::update(City::COLLECTION,
                                    array("_id"=>new MongoId($idCity)),
                                    array('$set' => $city),
                                    array('upsert' => true));

                        //var_dump($new);
                        $res[] = $new ;
>>>>>>> Stashed changes
                    }
                }else{
                    $erreur .=  "<br> 'error' : 'cp exist déjà' , cp' : '".$cp."' , 'complement' : '".trim($cedex)."' , 'name' : '".trim($valueFile[2])."' ";
                } 
            }
        }

<<<<<<< Updated upstream
        echo "Il y a ".$nb."update" ;
        echo "<br><br>---------------------<br><br>";
        echo $erreur ;
        echo "<br><br>---------------------<br><br>";
        echo $bon ;
    } 
=======
                }
               /* else{
                    /// Appliquer Similar_text
                    $cp = $lineCSV[1];
                    $city = SIG::getCityByLatLngGeoShape($lat, $lon, $cp);
                    if(!empty($city)){
                        $idCity = (String)$city["_id"];
                        foreach ($city["postalCodes"] as $key => $value) {
                            if($value["postalCode"] == $cp){
                                similar_text($lineCSV[2], self::nameCities($value["name"]), $percent);
                                if(preg_match('#^[A-Z ]$#',$value["name"]) && $percent >= 97){ 
                                    $value["name"] = $lineCSV[2];
                                }
                            }
                        }
                        PHDB::update(City::COLLECTION,
                                    array("_id"=>new MongoId($idCity)),
                                    array('$set' => $city),
                                    array('upsert' => true));
                        //var_dump($new );
                        //$res[] = $new ;
                    }else{
                        $new["cp"] = $lineCSV[1];
                        $new["name"] = $lineCSV[2];
                        $res[] = $new ;
                    } 
                }*/
>>>>>>> Stashed changes




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
       // var_dump($elements);
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
}

