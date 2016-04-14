<?php
/*
    
 */
class Import
{ 
    const MICROFORMATS = "microformats";
    const ORGANIZATIONS = "organizations";
    const MAPPINGS = "mappings";
    

    public static function importData($file, $post) 
    {
        $params = array("createLink"=>false);
        if(isset($file['fileImport']))
        {
            $nameFile = explode(".", $file['fileImport']['name']) ;
            //var_dump($nameFile);
            if($nameFile[count($nameFile)-1] == "json" || $nameFile[count($nameFile)-1] == "js")
                $params = Import::parsingJSON($post);
            else if($nameFile[count($nameFile)-1] == "csv")
                $params = Import::parsingCSV($post);
                //$params = Import::parsingCSV($file, $post);
        }

        return $params ;
    }


   /* public static function parsingJSON($file, $post) 
    {
        header('Content-Type: text/html; charset=UTF-8');
        if(isset($file['fileImport']))
        {
            $json = file_get_contents($file['fileImport']['tmp_name']);
            
            $nameFile = $file['fileImport']['name'] ;
            $arrayNameFile = explode(".", $nameFile);
            
            $path = sys_get_temp_dir().'/filesImportData/' ;
            
            if(!file_exists($path))
                mkdir($path , 0775);

            $path = sys_get_temp_dir().'/filesImportData/'.$arrayNameFile[0].'/';
            if(!file_exists($path))
                mkdir($path , 0775);

            Import::createJSON($json, $nameFile, $path);

            $json_objet = json_decode($json, true);
            
            $chaine ="";
            foreach ($json_objet as $key => $value) {
                $chaine .= FileHelper::arbreJson($value , "", "");
            }

            $arbre = explode(";",  $chaine);
            $listBrancheJson = array();
            foreach ($arbre as $key => $value) 
            {
                if(!in_array($value, $listBrancheJson) && trim($value) != "")
                    $listBrancheJson[] = $value ;
            }
            
            $params = array("createLink"=>true,
                            "typeFile" => "json",
                            "arbre"=>$listBrancheJson,
                            "nameFile"=>$file['fileImport']['name'],
                            "json_origine"=>$json,
                            "idCollection"=>$post['chooseCollection']);
            
        }
        else
        {
            $params = array("createLink"=>false);
        }
        return $params ;

    }*/

    public static function createCSV ($arrayCSV, $nameFile, $path)
    {
        $csv = new SplFileObject($path.$nameFile, 'w');
        foreach ($arrayCSV as $lineCSV) {
            $csv->fputcsv($lineCSV);
        }
    }


    public static function updateCSV ($arrayCSV, $nameFile, $path)
    {
        $CSV = array();
        $fileCSV = new SplFileObject($path.$nameFile, 'r');
        $fileCSV->setFlags(SplFileObject::READ_CSV);
        $fileCSV->setCsvControl(',', '"', '"');
        $i = 0 ;
        while (!$fileCSV->eof()) {
            $CSV[] = $fileCSV->fgetcsv() ;
            $i++;
        }
        $conca = array_merge($CSV, $arrayCSV);

        $csv = new SplFileObject($path.$nameFile, 'w');
        foreach ($conca as $lineCSV) {
            $csv->fputcsv($lineCSV);
        }
    }


    public static function createJSON ($json, $nameFile, $path)
    {
        file_put_contents ($path.$nameFile , $json );
    }


    public static function parsingJSON2($post) 
    {
        header('Content-Type: text/html; charset=UTF-8');
        if(isset($post['file']))
        {

            $json = $post['file'][0];
            
            //activer
            /*$search = array("\t", "\n", "\r");
            $json = strip_tags (str_replace($search, " ", $json));*/
            
            /*$nameFile = $post['nameFile'] ;
            $arrayNameFile = explode(".", $nameFile);
            
            $path = sys_get_temp_dir().'/filesImportData/' ;
            
            if(!file_exists($path))
                mkdir($path , 0775);

            $path = sys_get_temp_dir().'/filesImportData/'.$nameFile .'/';
            if(!file_exists($path))
                mkdir($path , 0775);

            Import::createJSON($json, $nameFile, $path);
            $subFiles = scandir(sys_get_temp_dir()."/filesImportData/".$nameFile);*/
            
            $chaine ="";
            if(!empty($post["pathObject"])){
                $obj = json_decode($json, true);
                //var_dump($obj );
                $map = explode(".", $post["pathObject"]) ;
                //var_dump($map);
                $json_objet = ArrayHelper::getValueJson($obj, $map);
                //var_dump($json_objet);
                foreach ($json_objet as $key => $value) {
                    $chaine .= ArrayHelper::getAllBranchsJSON($value);
                }
            }else{
                //var_dump($json);
                $json_objet = json_decode($json, true);
                if(substr($json, 0,1) == "{")
                    $chaine .= ArrayHelper::getAllBranchsJSON($json_objet);
                else{
                    
                    foreach ($json_objet as $key => $value) {
                        $chaine .= ArrayHelper::getAllBranchsJSON($value);
                    }
                }
            }
                
            
            $arbre = explode(";",  $chaine);
            $listBrancheJson = array();
            foreach ($arbre as $key => $value) 
            {
                if(!in_array($value, $listBrancheJson) && trim($value) != "")
                    $listBrancheJson[] = $value ;
            }

            $params = array("_id"=>new MongoId($post['idMicroformat']));
            $fields = array("mappingFields");
            $fieldsCollection = Import::getMicroFormats($params, $fields);
            $arrayMicroformat = array();
            foreach ($fieldsCollection as $key => $value) 
            {
                $pathMapping = ArrayHelper::getAllBranchsJSON($value['mappingFields'], "", "");
                $arrayPathMapping = explode(";",  $pathMapping);
                foreach ($arrayPathMapping as $keyPathMapping => $valuePathMapping) 
                {
                    
                    if(!empty($valuePathMapping))
                        $arrayMicroformat[] =  $valuePathMapping;
                }
            }
            
            $params = array("createLink"=>true,
                            "arbre"=>$listBrancheJson,
                            "typeFile" => "json",
                            "arrayMicroformat"=>$arrayMicroformat);
                            
                            //"nameFile"=>$nameFile ,
                            
                            //"json_origine"=>$json,
                           // "jsonData"=>json_encode($json_objet),
                             //"subFiles" => $subFiles,
                            
                            //"idCollection"=>$post['chooseCollection']);
            
        }
        else
        {
            $params = array("createLink"=>false);
        }
        return $params ;

    }

    public static function parsingCSV2($post) 
    {
        
        $arrayMicroformat = self::getMicroformat($post['idMicroformat']);

        if($post['idMapping'] != "-1"){
            $where = array("_id" => new MongoId($post['idMapping']));
            $fields = array("fields");
            $mapping = self::getMappings($where, $fields);
            $arrayMapping = $mapping[$post['idMapping']]["fields"];
        }
        else
            $arrayMapping = array();

        $params = array("createLink"=>true,
                        "arrayMicroformat"=>$arrayMicroformat,
                        "arrayMapping"=>$arrayMapping,
                        "typeFile"=>$post['typeFile']);
        return $params ;
    }


    public static function getMicroformat($idMicroformat){
        
        $params = array("_id"=>new MongoId($idMicroformat));
        $fields = array("mappingFields");
        $fieldsCollection = Import::getMicroFormats($params, $fields);
        $arrayPathMapping2 = array();
        foreach ($fieldsCollection as $key => $value) 
        {
            $pathMapping = ArrayHelper::getAllBranchsJSON($value['mappingFields']);
            $arrayPathMapping = explode(";",  $pathMapping);
            foreach ($arrayPathMapping as $keyPathMapping => $valuePathMapping) 
            {
                
                if(!empty($valuePathMapping))
                    $arrayPathMapping2[] =  $valuePathMapping;
            }
        }     

        return $arrayPathMapping2 ;
    }

    /*public static function parsingCSV($file, $post) 
    {
        header('Content-Type: text/html; charset=UTF-8');
        if(isset($file['fileImport']) && isset($post['separateurDonnees']) && isset($post['separateurTexte']) && isset($post['chooseCollection']))
        {
            $csv = new SplFileObject($file['fileImport']['tmp_name'], 'r');
            $csv->setFlags(SplFileObject::READ_CSV);
            
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

        //$data["tags"][] = "NuitDebout";

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

            
            $params = array("result" => true,
                            "jsonImport"=> json_encode($arrayJson),
                            "jsonError"=> json_encode($arrayJsonError),
                            "listEntite"=> $listEntite,
                            "geo"=>$notGeo);
        }

        return $params;

    }
    
    public static function previewData($post) 
    {
        $params = array("createLink"=>false);
        //var_dump($post['typeFile']);

        if(isset($post['typeFile']))
        {
            if($post['typeFile'] == "json" || $post['typeFile'] == "js" || $post['typeFile'] == "geojson")
                $params = Import::previewDataJSON($post);
            else if($post['typeFile'] == "csv")
                $params = Import::previewDataCSV($post);
        }

        return $params ;

    }
    public static function createOrUpdateJsonForImport($post){

        $path = '../../modules/cityData/importData/' ;
        if(!file_exists($path))
            mkdir($path , 0775);

        $path .= $post['nameFile'].'/';
        if(!file_exists($path))
            mkdir($path , 0775);

        $pathSystem = sys_get_temp_dir().'/importData/' ;
        if(!file_exists($pathSystem))
            mkdir($pathSystem , 0775);

        $pathSystem .= $post['nameFile'].'/';
        if(!file_exists($pathSystem))
            mkdir($pathSystem , 0775);

        if(file_exists($path."dataImport.json") == true){
            $fileDataImport = file_get_contents($path."dataImport.json", FILE_USE_INCLUDE_PATH);
            $chaine = substr($fileDataImport, 1, strlen($fileDataImport)-2) .",".substr($post['jsonImport'], 1, strlen($post['jsonImport'])-2);
            $newFileDataImport = "[".$chaine."]";
        }else
            $newFileDataImport = $post['jsonImport'] ;

        file_put_contents($pathSystem."dataImport.json", $newFileDataImport);
        file_put_contents($path."dataImport.json", $newFileDataImport);

        if(file_exists($path."dataError.json") == true){
            $fileDataError = file_get_contents($path."dataError.json", FILE_USE_INCLUDE_PATH);
            $chaine = substr($fileDataError, 1, strlen($fileDataError)-2) .",".substr($post['jsonError'], 1, strlen($post['jsonError'])-2);
            $newFileDataError = "[".$chaine."]";
        }else
            $newFileDataError = $post['jsonError'] ;
        file_put_contents($path."dataError.json", $newFileDataError);

        if(file_exists($path."importMongo.sh") != true){
           $fileImportMongo = "mongoimport --db pixelhumain --collection ".$post['collection']." dataImport.json --jsonArray;\n";
           file_put_contents($path."importMongo.sh", $fileImportMongo);
        }
        
    }
    
    public static function importOrganizationsInMongo($post)
    {
        /***** new version *****/

        $newFolder = false ;

        $path = '../../modules/cityData/importData/' ;
        if(!file_exists($path))
            mkdir($path , 0775);


        $pathFile = '../../modules/cityData/importData/'.$post['nameFile'].'/' ;
        if(!file_exists($pathFile))
        {
            mkdir($pathFile , 0775);
            $count = 1 ;
            $newFolder = true ;
        }    
        else
        {
            $files = scandir($pathFile);
            $count = 1 ;
            foreach ($files as $key => $value) {
                $name_file = explode(".", $value);
                if (strpos($name_file[0], "cityData") !== false) 
                {
                   $count++;
                }
            }
        }

        //importmongo all
        if(file_exists("../../modules/cityData/importData/importAllMongo.sh") == true)
            $textImportMongoAll = file_get_contents("../../modules/cityData/importData/importAllMongo.sh", FILE_USE_INCLUDE_PATH);
        else
            $textImportMongoAll = "" ;

        if($newFolder)
        {
            $textImportMongoAll = $textImportMongoAll."cd ".$post['nameFile'].";\n";
            $textImportMongoAll = $textImportMongoAll."sh importMongo.sh;\n";
            $textImportMongoAll = $textImportMongoAll."cd .. ;\n";
        }

        //importmongo 
        if(file_exists("../../modules/cityData/importData/".$post['nameFile']."/importMongo.sh") == true)
            $textFileSh = file_get_contents("../../modules/cityData/importData/".$post['nameFile']."/importMongo.sh", FILE_USE_INCLUDE_PATH);
        else
            $textFileSh = "" ;


        $textFileSh = $textFileSh . "mongoimport --db pixelhumain --collection organizations ".$post['nameFile']."_".$count.".json --jsonArray;\n";
        
        file_put_contents("../../modules/cityData/importData/".$post['nameFile']."/".$post['nameFile']."_".$count.".json", $post['jsonImport']);
        file_put_contents("../../modules/cityData/importData/".$post['nameFile']."/error_".$count.".json", $post['jsonError']);
        file_put_contents("../../modules/cityData/importData/".$post['nameFile']."/importMongo.sh", $textFileSh);
        file_put_contents("../../modules/cityData/importData/importAllMongo.sh", $textImportMongoAll);

        if(isset($post['jsonImport']))
        {
            $arrayDataImport = json_decode($post['jsonImport'], true) ;

            foreach ($arrayDataImport as $key => $value) 
            {
                $newOrganization = Organization::newOrganizationFromImportData($value);
                try{
                    $resData[] = Organization::insert($newOrganization, $post['creatorID']) ; 
                }
                catch (CTKException $e){
                    $resData[] = $e->getMessage();
                }        
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


    public static function importProjectsInMongo($post)
    {
        /***** new version *****/

        $newFolder = false ;

        $path = '../../modules/cityData/importData/' ;
        if(!file_exists($path))
            mkdir($path , 0775);

        $pathFile = '../../modules/cityData/importData/'.$post['nameFile'].'/' ;
        if(!file_exists($pathFile))
        {
            mkdir($pathFile , 0775);
            $count = 1 ;
            $newFolder = true ;
        }    
        else
        {
            $files = scandir($pathFile);
            $count = 1 ;
            foreach ($files as $key => $value) {
                $name_file = explode(".", $value);
                if (strpos($name_file[0], "cityData") !== false) 
                {
                   $count++;
                }
            }
        }

        //importmongo all
        if(file_exists("../../modules/cityData/importData/importAllMongo.sh") == true)
            $textImportMongoAll = file_get_contents("../../modules/cityData/importData/importAllMongo.sh", FILE_USE_INCLUDE_PATH);
        else
            $textImportMongoAll = "" ;

        if($newFolder)
        {
            $textImportMongoAll = $textImportMongoAll."cd ".$post['nameFile'].";\n";
            $textImportMongoAll = $textImportMongoAll."sh importMongo.sh;\n";
            $textImportMongoAll = $textImportMongoAll."cd .. ;\n";
        }

        //importmongo 
        if(file_exists("../../modules/cityData/importData/".$post['nameFile']."/importMongo.sh") == true)
            $textFileSh = file_get_contents("../../modules/cityData/importData/".$post['nameFile']."/importMongo.sh", FILE_USE_INCLUDE_PATH);
        else
            $textFileSh = "" ;


        $textFileSh = $textFileSh . "mongoimport --db pixelhumain --collection organizations ".$post['nameFile']."_".$count.".json --jsonArray;\n";
        
        file_put_contents("../../modules/cityData/importData/".$post['nameFile']."/".$post['nameFile']."_".$count.".json", $post['jsonImport']);
        file_put_contents("../../modules/cityData/importData/".$post['nameFile']."/error_".$count.".json", $post['jsonError']);
        file_put_contents("../../modules/cityData/importData/".$post['nameFile']."/importMongo.sh", $textFileSh);
        file_put_contents("../../modules/cityData/importData/importAllMongo.sh", $textImportMongoAll);

        if(isset($post['jsonImport'])){
            $arrayDataImport = json_decode($post['jsonImport'], true) ;
            $resData =  array();
            foreach ($arrayDataImport as $key => $value){
                try{
                    $resData[] = Project::insertProjetFromImportData($value, $post['creatorID'],Person::COLLECTION) ; 
                }
                catch (CTKException $e){
                    $resData[] = $e->getMessage();
                }        
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


    public static function createArrayList($list) {
        $head2 = array("name", "warnings", "msgError") ;
        $tableau = array();
        $tableau[] = $head2 ;
        //var_dump($list);
        foreach ($list as $keyList => $valueList){
            $ligne = array();
            $ligne[] = (empty($valueList["name"])? "" : $valueList["name"]);
            $ligne[] = (empty($valueList["warnings"])? "" : self::getMessagesWarnings($valueList["warnings"]));
            $ligne[] = (empty($valueList["msgError"])? "" : $valueList["msgError"]);
            $tableau[] = $ligne ;
        }
        return $tableau ;
    }


    public static function getMicroFormats($params, $fields=null, $limit=0) 
    {
        $microFormats =PHDB::findAndSort(self::MICROFORMATS,$params, array("created" =>1), $limit, $fields);
        return $microFormats;
    }


    public static function getDataByUrl($url){
        //  Initiate curl
        $ch = curl_init();
        // Disable SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // Will return the response, if false it print the response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Set the url
        curl_setopt($ch, CURLOPT_URL,$url);
        // Execute
        $result=curl_exec($ch);
        // Closing
        curl_close($ch);
        return $result;
    }






    public static function addDataInDb($post, $moduleId = null)
    {
        $jsonString = $post["file"];
        $typeEntity = $post["chooseEntity"];
        $pathFolderImage = $post["pathFolderImage"];

        if(substr($jsonString, 0,1) == "{")
            $jsonArray[] = json_decode($jsonString, true) ;
        else
            $jsonArray = json_decode($jsonString, true) ;

        if(isset($jsonArray)){
            $resData =  array();
            foreach ($jsonArray as $key => $value){
                try{

                    if($typeEntity == "project")
                        $res = Project::insertProjetFromImportData($value, $post['creatorID'],Person::COLLECTION,true,$pathFolderImage) ;
                    else if($typeEntity == "organization")
                        $res = Organization::insertOrganizationFromImportData($value, $post['creatorID'],true,$pathFolderImage, $moduleId) ;
                    else if($typeEntity == "person")
                        $res = Person::insertPersonFromImportData($value,null, true, $pathFolderImage, $moduleId) ;
                    else if($typeEntity == "invite")
                        $res = Person::insertPersonFromImportData($value,true, true, $pathFolderImage, $moduleId) ;
                    else if($typeEntity == "event")
                        $res = Event::insertEventFromImportData($value,true);


                    if(!empty($post["link"])){
                        Link::fo
                    }


                    if($res["result"] == true){
                        $entite["name"] =  $value["name"];
                        $entite["info"] = "Success";
                    }else{
                        $entite["name"] =  $value["name"];
                        $entite["info"] = "Error";
                    }
                    $resData[] = $entite;
                }
                catch (CTKException $e){
                    $entite["name"] =  $value["name"];
                    $entite["info"] = $e->getMessage();
                    $resData[] = $entite;
                }        
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
    

    public static function alternateCP($post){
        //var_dump(count($post['file']));
        foreach ($post['file'] as $key => $ligne){
            $set = array();
            $colonne = explode(",", $ligne[0]);
            if($key == 0){
                $cp[] = $colonne ;
                $erreur[] = $colonne ;
            }
            
            if($key > 0 && count($colonne) > 0){
                if($colonne[0] != "FALSE"){
                    //var_dump($colonne[0]);
                    if(strlen($colonne[2]) > 5){
                        $city = PHDB::findOne(City::COLLECTION, array("cp" => $colonne[14],"insee" => $colonne[13], "alternateName" => $colonne[16]));

                        if(!empty($city)){
                           $id = (String)$city["_id"] ;
                            $code = substr($colonne[2],0,5) ;
                            $complement = trim(substr($colonne[2],5,strlen($colonne[2])-1)) ;
                            $newCP["code"] = $code;
                            $newCP["complement"] = $complement;
                        
                            if(!empty($city["alternateCP"]))
                                $set["alternateCP"] = $city["alternateCP"];
                            
                            $set["alternateCP"][] = $newCP;

                            //var_dump($set);
                            /*PHDB::update( Organization::COLLECTION, 
                                        array("_id" => new MongoId($id)),
                                        array('$set' => $set));*/
                        }

                    }else{
                        $cp[] = $colonne ;
                    }

                }else{
                    //var_dump($colonne[2]) ;
                    if(count($colonne) > 0)
                        $erreur[] = $colonne ;
                }
                
            }

        }

        //var_dump( $cp) ;
        //var_dump( $cp) ;
        
        $path = '../../modules/cityData/filesImportData/' ;
        if(!file_exists($path))
            mkdir($path , 0775);

        $path = '../../modules/cityData/filesImportData/CEDEX/';
        if(!file_exists($path))
            mkdir($path , 0775);
       // $scanDir = scandir($path);

        /*$countCEDEX = 0 ;
        $countFALSE = 0 ;
        foreach ($scanDir as $key => $value) {
            $name_file = explode(".", $value);
            if (strpos($name_file[0], "CEDEXERREUR") !== false) 
                $countFALSE++;

            if (strpos($name_file[0], "CP") !== false) 
                $countCEDEX++;
        }


        Import::createCSV($erreur,"CEDEXERREUR_".$countFALSE.".csv", $path);
        Import::createCSV($cp,"CP_".$countCEDEX.".csv", $path);*/

        //Import::createCSV($erreur,"CEDEXERREUR.csv", $path);
        //Import::createCSV($cp,"CP.csv", $path);

        Import::updateCSV($erreur,"CEDEXERREUR.csv", $path);
        Import::updateCSV($cp,"CP.csv", $path);

        return array();
    }


    public static function getAndCheckWarnings($warnings) 
    {
        $newWarnings = array();
        $warningsUseless = array();

        if(in_array("101", $warnings) && in_array("102", $warnings) && in_array("105", $warnings) && in_array("103", $warnings) && in_array("104", $warnings) && !in_array("150", $warningsUseless))
        {
            $warningsUseless[] = "101";
            $warningsUseless[] = "102";
            $warningsUseless[] = "103";
            $warningsUseless[] = "104";
            $warningsUseless[] = "105";
            $warningsUseless[] = "150";
            $newWarnings[] = "100";
        }    

        if(in_array("151", $warnings) && in_array("152", $warnings) && !in_array("150", $warningsUseless)){
            $warningsUseless[] = "151";
            $warningsUseless[] = "152";
            $warningsUseless[] = "150";
            $newWarnings[] = "150";
        }

        foreach ($warnings as $key => $codeWarning) {
           if(!in_array($codeWarning, $warningsUseless) && !in_array($codeWarning, $newWarnings)){
                $newWarnings[] = $codeWarning;
           }
        }

        return $newWarnings;
    }

    public static function getMessagesWarnings($warnings) 
    {
        $msg = "";
        foreach ($warnings as $key => $codeWarning) {
            if($msg != "")
                $msg .= "<br/>";
            $msg .= Yii::t("import",$codeWarning, null, Yii::app()->controller->module->id);
        }

        return $msg;
    }

    public static function getLocalityByLatLonNominatim($lat, $lon){
        $url = "http://nominatim.openstreetmap.org/reverse?format=json&lat=".$lat."&lon=".$lon."&zoom=18&addressdetails=1" ;
        $options = array(
            "http"=>array(
                "header"=>"User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }


    public static function getGeoByAddressNominatim($street = null, $cp = null, $city = null, $country = null, $polygon_geojson = null){
        
        $url = "http://nominatim.openstreetmap.org/search?format=json&addressdetails=1" ;
        $urlminimiun = "http://nominatim.openstreetmap.org/search?format=json&addressdetails=1" ;
        if(!empty($street))
            $url .= "&street=".str_replace(" ", "+", $street);
        
        if(!empty($cp)){
            $url .= "&postalcode=".$cp;
            $urlminimiun .= "&postalcode=".$cp;
        }
            
        if(!empty($city)){
            $url .= "&city=".str_replace(" ", "+", $city);
            $urlminimiun .= "&city=".str_replace(" ", "+", $city);
        }
            
        
        /*if(!empty($country))
            $url .= "&countrycodes=".$country;*/
        
        if(!empty($polygon_geojson)){
            $url .= "&polygon_geojson=1";
            $urlminimiun .= "&polygon_geojson=1";
        }
            
        $result = file_get_contents($url);

        if($result == "[]"){
            $result = file_get_contents($urlminimiun);
        }
        
        return $result;
    }    


    public static function getLocalityByLatLonDataGouv($lat, $lon){
        $url = "http://api-adresse.data.gouv.fr/reverse/?lon=".$lon."&lat=".$lat."&zoom=18&addressdetails=1" ;
        $json = file_get_contents($url);
        return $json ;
    }

    public static function getGeoByAddressDataGouv($lat, $lon){
        $url = "http://api-adresse.data.gouv.fr/reverse/?lon=".$lon."&lat=".$lat."&zoom=18&addressdetails=1" ;
        $json = file_get_contents($url);
        return $json ;
    }


    public static function getAllEntitiesByKey($key){
        $result = array();
        $organizations = array();
        $events = array();
        
        $res = PHDB::find(Project::COLLECTION, array("source.key"=>$key, "state" => "uncomplete"));
        foreach ($res as $key => $value) {
            $projects = array();
            $projects["id"] = $key;
            $projects["name"] = $value["name"];
            $projects["warnings"] = $value["warnings"];
            $result["project"][] = $projects;
        }

        $res = PHDB::find(Person::COLLECTION, array("source.key"=>$key));
        foreach ($res as $key => $value) {
            $person = array();
            $person["id"] = $key;
            $person["name"] = $value["name"];
            if(!empty($value["warnings"]))
                $person["warnings"] = $value["warnings"];
            else
                $person["warnings"] = array();
            $result["person"][] = $person;
        }

        $res = PHDB::find(Organization::COLLECTION, array("source.key"=>$key));
        foreach ($res as $key => $value) {
            $person = array();
            $person["id"] = $key;
            $person["name"] = $value["name"];
            if(!empty($value["warnings"]))
                $person["warnings"] = $value["warnings"];
            else
                $person["warnings"] = array();
            $result["organization"][] = $person;
        }

        $res = PHDB::find(Event::COLLECTION, array("source.key"=>$key));
        foreach ($res as $key => $value) {
            $person = array();
            $person["id"] = $key;
            $person["name"] = $value["name"];
            if(!empty($value["warnings"]))
                $person["warnings"] = $value["warnings"];
            else
                $person["warnings"] = array();
            $result["event"][] = $person;
        }

        return $result ;
    }


    public static function getMappings($where=array(),$fields=null){
        $allMapping = PHDB::find(self::MAPPINGS, $where, $fields);
        return $allMapping;
    }


    public static function getAndCheckAddressForEntity($address = null, $geo = null, $warnings = null){
        
        $details["warnings"] = array();
        $newAddress = array(    '@type' => 'PostalAddress',
                                 'streetAddress' =>  '', 
                                 'postalCode' =>  '',
                                 'addressLocality' =>  '',
                                 'addressCountry' =>  '',
                                 'codeInsee' =>  '');

        $newGeo["geo"] = array(  "@type"=>"GeoCoordinates",
                        "latitude" => "",
                        "longitude" => "");

        //Cas 1 Pas d'adresse , ni geo
        if(empty($address) && empty($geo)){
             if($warnings){
                $details["warnings"][] = "100";
                $details["warnings"][] = "150";
             }    
             else
                 throw new CTKException(Yii::t("import","100", null, Yii::app()->controller->module->id));
        }//Cas 2 On a que l'addresse
        else if(!empty($address) && empty($geo)){

            if(!empty($address["streetAddress"])){
                $street = $address["streetAddress"] ;
                $newAddress["streetAddress"] = $address["streetAddress"];
            }  
            else
                $street = null ;
            if(!empty($address["postalCode"])){
                $cp = $address["postalCode"] ;
                $newAddress["postalCode"] = $cp ;
            } 
            else
                $cp = null ;
            if(!empty($address["addressCountry"]))
                $country = $address["addressCountry"] ;
            else
                $country = null ;
            if(!empty($address["addressLocality"]))
                $city = $address["addressLocality"] ;
            else
                $city = null ;

            $resultNominatim = json_decode(self::getGeoByAddressNominatim($street, $cp, $city, $country), true);
            
            if(!empty($resultNominatim[0])){
                
                $newGeo["geo"]["latitude"] = $resultNominatim[0]["lat"];
                $newGeo["geo"]["longitude"] = $resultNominatim[0]["lon"];
                
                $city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"],(empty($cp) ? null : $cp) );
                
                if(!empty($city)){
                    //foreach ($city as $key => $value){
                        $newAddress["codeInsee"] = $city["insee"];
                        $newAddress['addressCountry'] = $city["country"];
                        foreach ($city["postalCodes"] as $keyCp => $valueCp){
                            if($valueCp["postalCode"] == $cp){
                                $newAddress['addressLocality'] = $valueCp["name"];
                            }
                        }
                    //    break;
                    //}
                }
            }
        } // Cas 3 il n'y a que la Géo 
        else if(empty($address) && !empty($geo)){
            if(empty($geo["latitude"])){
                if($warnings)
                    $details["warnings"][] = "151";
                else
                    throw new CTKException(Yii::t("import","151", null, Yii::app()->controller->module->id));
            }

            if(empty($geo["longitude"])){
                if($warnings)
                    $details["warnings"][] = "152";
                else
                     throw new CTKException(Yii::t("import","152", null, Yii::app()->controller->module->id));
            }
            if(!empty($geo["latitude"]) && !empty($geo["longitude"])){
                $newGeo["geo"]["latitude"] = $geo["latitude"] ;
                $newGeo["geo"]["longitude"] =  $geo["longitude"] ;
                $resultNominatim = json_decode(self::getLocalityByLatLonNominatim($geo["latitude"], $geo["longitude"]), true);
            }  
                
            
            if(!empty($resultNominatim)){
                if($resultNominatim["address"]["country_code"] == "fr"){

                    
                    $arrayCP = explode(";", $resultNominatim["address"]["postcode"]);
                    $city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"],(empty($arrayCP[0]) ? null : $arrayCP[0]) );
                    //$city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"], null);
                
                    if(!empty($city)){
                        //foreach ($city as $key => $value){
                            $newAddress["codeInsee"] = $city["insee"];
                            $newAddress['addressCountry'] = $city["country"];
                            
                            $newAddress['postalCode'] = $arrayCP[0];
                            foreach ($city["postalCodes"] as $keyCp => $valueCp){
                                if($valueCp["postalCode"] == $arrayCP[0]){
                                    $newAddress['addressLocality'] = $valueCp["name"];
                                }
                            }
                        //    break;
                        //}
                    }
                }else{
                    throw new CTKException("N'est pas en France");
                }
            }
            

        } // Cas 4 Il y a les 2
        else if(!empty($address) && !empty($geo)){
            /*$newAddress["streetAddress"] = (empty($address["streetAddress"])?"":$address["streetAddress"]) ;
            $newAddress["postalCode"] = (empty($address["postalCode"])?"":$address["postalCode"]) ;
            $newAddress["addressLocality"] = (empty($address["addressLocality"])?"":$address["addressLocality"]) ;
            $newAddress["addressCountry"] = (empty($address["addressCountry"])?"":$address["addressCountry"]) ;
            $newAddress["codeInsee"] = (empty($address["codeInsee"])?"":$address["codeInsee"]) ;*/
           
            $newGeo["geo"]["latitude"] = (empty($geo["latitude"])?"":$geo["latitude"]) ;
            $newGeo["geo"]["longitude"] = (empty($geo["longitude"])?"":$geo["longitude"]) ;


            if(!empty($address["streetAddress"])){
                $street = $address["streetAddress"] ;
                $newAddress["streetAddress"] = $address["streetAddress"];
            }  
            else
                $street = null ;

            if(!empty($address["postalCode"])){
                $cp = $address["postalCode"] ;
                $newAddress["postalCode"] = $cp ;
            } 
            else
                $cp = null ;
            if(!empty($address["addressCountry"]))
                $country = $address["addressCountry"] ;
            else
                $country = null ;
            if(!empty($address["addressLocality"]))
                $city = $address["addressLocality"] ;
            else
                $city = null ;

            $resultNominatim = json_decode(self::getGeoByAddressNominatim($street, $cp, $city, $country), true);
            
            if(!empty($resultNominatim[0])){
                
                //Comparer la geo avec nominatim
                

                //
                //var_dump($cp);

            }
            $city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"],(empty($cp) ? null : $cp) );
            
            if(!empty($city)){
                //foreach ($city as $key => $value){
                    $newAddress["codeInsee"] = $city["insee"];
                    $newAddress['addressCountry'] = $city["country"];
                    foreach ($city["postalCodes"] as $keyCp => $valueCp){
                        if($valueCp["postalCode"] == $cp){
                            $newAddress['addressLocality'] = $valueCp["name"];
                        }
                    }
                //    break;
                //}
            }
            
        
        }

        if(!empty($newGeo["geo"]["latitude"]) && !empty($newGeo["geo"]["longitude"])){

            $newGeo["geoPosition"] = array("type"=>"Point",
                                                "coordinates" =>
                                                    array(
                                                        floatval($newGeo["geo"]['latitude']),
                                                        floatval($newGeo["geo"]['longitude'])));
            $details["geo"] = $newGeo["geo"];
            $details["geoPosition"] = $newGeo["geoPosition"];
        }
        
                                                   
        
        /*//On test si le code postal est dans la BD
        if(!empty($address['postalCode'])){
             $cityByCp = PHDB::find(City::COLLECTION, array("cp"=>$address['postalCode']));
             //var_dump($cityByCp);
             if(empty($cityByCp)){
                 if($warnings)
                     $details["warnings"][] = "106";
                 else
                     throw new CTKException(Yii::t("import","106", null, Yii::app()->controller->module->id));
 
             }
                 
         }
         //On a besoin de récupere la locality, la country, l'insee
         //Si on a la latitude et la longitude 
         if(!empty($geo["latitude"]) && !empty($geo["longitude"])){
             //On récupere la city correspondant a la latitude, la longitude et le code postal
             $city = SIG::getInseeByLatLngCp($geo["latitude"], $geo["longitude"],(empty($address['postalCode']) ? null : $address['postalCode']) );
             if(!empty($city)){
                 foreach ($city as $key => $value){
                     $insee = $value["insee"];
                    $cp = $value["cp"];
                     $newAddress['addressCountry'] = $value["country"];
                     $newAddress['addressLocality'] = $value["alternateName"];
                     break;
                 }
             }
             else{
                 //On va parcourir les cities récuperer via le cp
                 if(!empty($cityByCp)) {
                     $find = false ;
                     foreach ($cityByCp as $key => $value){
                         //On test si l'alternateName ou le name corresponds à la Locality se trouvant dans $address
                         if($value["alternateName"] == $address['addressLocality'] || $value["name"] == $address['addressLocality']){
                             $insee = $value["insee"];
                             $cp = $value["cp"];
                             $newAddress['addressCountry'] = $value["country"];
                             $newAddress['addressLocality'] = $value["alternateName"];
                             $find = true ;
                             break;
                         }
                     }
                if($find == false){
                         if($warnings)
                             $details["warnings"][] = "110";
                         else
                             throw new CTKException(Yii::t("import","110", null, Yii::app()->controller->module->id));
                     } 
                         
                 }
                 $newProject['warnings'][] = "170";
             }   
         }else{
            $find = false;
             foreach ($cityByCp as $key => $value){
                 //On test si l'alternateName ou le name corresponds à la Locality se trouvant dans $address
                 if($value["alternateName"] == $address['addressLocality'] || $value["name"] == $address['addressLocality']){
                     $insee = $value["insee"];
                     $cp = $value["cp"];
                     $newAddress['addressCountry'] = $value["country"];
                     $newAddress['addressLocality'] = $value["alternateName"];
                     $find = true ;
                     break;
                 }
             }
             if($find == false){
                 if($warnings)
                     $details["warnings"][] = "110";
                 else
                     throw new CTKException(Yii::t("import","110", null, Yii::app()->controller->module->id));
             }
         }   
 
         //Afin d'éviter des incohérences, on test si l'insee fournir par $address et l'insee sont identique
         if(!empty($insee) && !empty($address['codeInsee']) ){
             if($insee == $address['codeInsee'])
                 $newAddress['codeInsee'] = $insee ;
             else{
                 if($warnings)
                     $details["warnings"][] = "171";
                 else
                     throw new CTKException(Yii::t("import","171", null, Yii::app()->controller->module->id));
             }}else if(!empty($insee)){
             $newAddress['codeInsee'] = $insee ;
         }else if(!empty($address['codeInsee'])){
             $newAddress['codeInsee'] = $address['codeInsee'];
         }
 
         //Même chose pour le code postal
         if(!empty($address['postalCode']) && !empty($cp)){
             if($cp == $address['postalCode'])
                 $newAddress['postalCode'] = $cp ;
             else{
                 if($warnings)
                     $detail["warnings"][] = "172";
                 else
                     throw new CTKException(Yii::t("import","172", null, Yii::app()->controller->module->id));
             }
                 
         }else if(!empty($cp)){
             $newAddress['postalCode'] = $cp ;
         }else if(!empty($address['postalCode'])){
             $newAddress['postalCode'] = $address['postalCode'];
         }
 
         if(!empty($address['streetAddress']))
             $newAddress['streetAddress'] = $address['streetAddress'];
        */
        $details["address"] = $newAddress;

        return $details;
    } 
}

