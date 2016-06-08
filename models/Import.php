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
            $data["source"]['key'][] = $post['key'];

        if(!empty($post['badge']))
            $data["badges"][] = $post['badge'];

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

    public static function createZipForImport($post){

        $jsonImport = json_decode($post['jsonImport'], true);
        $jsonError = json_decode($post['jsonError'], true);

        //Good
        $objects = array();
        $arrayNameFile = array();
        $nbFile = 1 ;

        foreach ($jsonImport as $key => $value) {
            $objects[] = $value ;
            if(count($objects) >= 5000 ){
                //create file in tmp
                file_put_contents(sys_get_temp_dir()."/entityImport".$nbFile.".json", json_encode($objects));
                $arrayNameFile[] = "entityImport".$nbFile.".json" ;
                $objects = array();
                $nbFile++;
            }
        }
        file_put_contents(sys_get_temp_dir()."/entityImport".$nbFile.".json", json_encode($objects));
        $arrayNameFile[] = "entityImport".$nbFile.".json" ;
        

        //Error
        $objects = array();
        $nbFile = 1 ;
        foreach ($jsonError as $key => $value) {
            $objects[] = $value ;
            if(count($objects) >= 5000 ){
                //create file in tmp
                file_put_contents(sys_get_temp_dir()."/entityError".$nbFile.".json", json_encode($objects));
                $arrayNameFile[] = "entityError".$nbFile.".json" ;
                $objects = array();
                $nbFile++;
            }
        }
        file_put_contents(sys_get_temp_dir()."/entityError".$nbFile.".json", json_encode($objects));
        $arrayNameFile[] = "entityError".$nbFile.".json" ;

        //File Import.sh
        $fileImportMongo = "";
        foreach ($arrayNameFile as $key => $value) {
            $fileImportMongo .= "mongoimport --db pixelhumain --collection ".$post['collection']." ".$value." --jsonArray;\n";
        }
        file_put_contents(sys_get_temp_dir()."/importMongo.sh", $fileImportMongo);

        $zip = new ZipArchive();
        $filename = sys_get_temp_dir()."/import.zip";

        if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
            echo "Impossible d'ouvrir le fichier" ;
        }
        foreach ($arrayNameFile as $key => $value) {
            $zip->addFile(sys_get_temp_dir()."/".$value, $value);        
        }
        $zip->close();

       // $files1 = scandir(sys_get_temp_dir());
        // var_dump($files1);
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=import.zip');
        header('Content-Length: ' . filesize(sys_get_temp_dir()."/import.zip"));


        readfile(sys_get_temp_dir()."/import.zip", true);


        /*header('Pragma: public');
        header('Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0');
         
        header('Content-Tranfer-Encoding: none');
        header('Content-Length: '.filesize(sys_get_temp_dir()."/import.zip"));
        header('Content-MD5: '.base64_encode(md5_file(sys_get_temp_dir()."/import.zip")));
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename=import.zip');
         
        //header('Date: '.date());
        header('Expires: '.gmdate(DATE_RFC1123, time()+1));
        header('Last-Modified: '.gmdate(DATE_RFC1123, filemtime(sys_get_temp_dir()."/import.zip")));
        readfile(sys_get_temp_dir()."/import.zip", true);*/
        //$path_parts = pathinfo(sys_get_temp_dir()."/import.zip");
        //return $path_parts['dirname'].$path_parts['basename'];

        //exit;
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

        if($newFolder){
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

        $sendMail = ($post["sendMail"] == "false"?null:true);
        $isKissKiss = ($post["isKissKiss"] == "false"?null:true);
        
        if(substr($jsonString, 0,1) == "{")
            $jsonArray[] = json_decode($jsonString, true) ;
        else
            $jsonArray = json_decode($jsonString, true) ;

        if(isset($jsonArray)){
            $resData =  array();
            foreach ($jsonArray as $key => $value){
                try{
                    
                    if($post["link"] == "true"){
                        $paramsLink = array();
                        $paramsLink["link"] = true;
                        $paramsLink["idLink"] = $post["idLink"];
                        $paramsLink["typeLink"] = $post["typeLink"];
                        if($post["isAdmin"] == "true")
                            $paramsLink["isAdmin"] = true;
                        else
                            $paramsLink["isAdmin"] = false;
                    }else{
                        $paramsLink = null;
                    }

                    if($typeEntity == "project")
                        $res = Project::insertProjetFromImportData($value, $post['creatorID'],Person::COLLECTION,true,$pathFolderImage, $paramsLink) ;
                    else if($typeEntity == "organization")
                        $res = Organization::insertOrganizationFromImportData($value, $post['creatorID'],true,$pathFolderImage, $moduleId, $paramsLink) ;
                    else if($typeEntity == "person")
                        $res = Person::insertPersonFromImportData($value,null, true, $isKissKiss, $pathFolderImage, $moduleId, $paramsLink) ;
                    else if($typeEntity == "invite")
                        $res = Person::insertPersonFromImportData($value,true, true, $isKissKiss, $pathFolderImage, $moduleId, $paramsLink,  $sendMail) ;
                    else if($typeEntity == "event")
                        $res = Event::insertEventFromImportData($value,true, $post["link"]);


                    //var_dump($res);
                    if($res["result"] == true){
                        $entite["name"] =  $value["name"];
                        $entite["info"] = "Success";
                    }else{
                        $entite["name"] =  $value["name"];
                        $entite["info"] = $res["msg"];
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
        //$urlminimiun = "http://nominatim.openstreetmap.org/search?format=json&addressdetails=1" ;
        if(!empty($street))
            $url .= "&street=".str_replace(" ", "+", $street);
        
        if(!empty($cp)){
            $url .= "&postalcode=".$cp;
        }
            
        if(!empty($city)){
            $url .= "&city=".str_replace(" ", "+", $city);
        }
            
        
        /*if(!empty($country))
            $url .= "&countrycodes=".$country;*/
        
        //
        if(!empty($polygon_geojson)){
            $url .= "&polygon_geojson=1";
        }

        /*$options = array(
            "http"=>array(
                "header"=>"User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);*/
        //var_dump($url);
        $result = Import::getUrl($url);
        
        return $result;
    }



    public static function getGeoByAddressMinimunNominatim($cp = null, $city = null, $country = null, $polygon_geojson = null){
        
        $urlminimiun = "http://nominatim.openstreetmap.org/search?format=json&addressdetails=1" ;
        if(!empty($cp)){
            $urlminimiun .= "&postalcode=".$cp;
        }
            
        if(!empty($city)){
            $urlminimiun .= "&city=".str_replace(" ", "+", $city);
        }
        if(!empty($country))
            //$urlminimiun .= "&countrycodes=".$country;
        
        if(!empty($polygon_geojson)){
            $urlminimiun .= "&polygon_geojson=1";
        }
        /*$options = array(
            "http"=>array(
                "header"=>"User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($urlminimiun, false, $context);
        //var_dump($urlminimiun) ;*/
        return Import::getUrl($urlminimiun) ;
    }    


   public static function getGeoByAddressGoogleMap($street = null, $cp = null, $city = null, $country = null, $polygon_geojson = null){
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" ;

        if(!empty($street))
            $url .= str_replace(" ", "+", $street);
        
        if(!empty($cp)){
            if(!empty($street))
                $url .= "+".$cp;
            else
                $url .= $cp;
        }
            
        if(!empty($city)){
            $url .= "+".str_replace(" ", "+", $city);
        }

        if(!empty($country)){
            $url .= "+".$country;
        }

        $url = $url . "&key=".Yii::app()->params['google']['keyMaps'] ;
        //var_dump($url);
        
        /*$options = array(
            "http"=>array(
                "header"=>"User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        //var_dump($url) ;
        //$json = file_get_contents($url);*/

        return Import::getUrl($url) ;
    }

    public static function getGeoByAddressMinimunGoogleMap($cp = null, $city = null, $country = null){
        $urlminimiun = "https://maps.googleapis.com/maps/api/geocode/json?address=" ;

        if(!empty($cp)){
            $urlminimiun .= $cp;
        }
            
        if(!empty($city)){
            $urlminimiun .= "+".str_replace(" ", "+", $city);
        }


        if(!empty($country)){
            $urlminimiun .= "+".$country;
        }

        $urlminimiun = $urlminimiun . "&key=".Yii::app()->params['google']['keyMaps'] ;
        /*$options = array(
            "http"=>array(
                "header"=>"User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($urlminimiun, false, $context);*/
        //$result = file_get_contents($urlminimiun);  
        //var_dump($urlminimiun) ;
        return Import::getUrl($urlminimiun) ;
    }


    public static function getGeoByAddressDataGouv($street = null, $cp = null, $city = null, $polygon_geojson = null){
        $url = "http 'http://api-adresse.data.gouv.fr/search/?q=" ;
        if(!empty($street))
            //$url .= $street ;
            $url .= str_replace(" ", "+", $street);
        
        if(!empty($city)){
            //$url .= " ".$city ;
            $url .= "+".str_replace(" ", "+", $city);
        }

        if(!empty($cp)){
            $url .= "&postcode=".$cp;
        }
        $url .= "&type=street";   
        
        /*$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        //var_dump($url) ;*/

        
        return Import::getUrl($url) ;
    }


    public static function getUrl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result ;
    }

    public static function getGeoByAddressMinimunDataGouv($cp = null, $city = null, $polygon_geojson = null){
        $url = "http 'http://api-adresse.data.gouv.fr/search/?q=" ;
        
        if(!empty($city)){
            $url .= "+".str_replace(" ", "+", $city);
        }

        if(!empty($cp)){
            $url .= "&postcode=".$cp;
        }
            
        $options = array(
            "http"=>array(
                "header"=>"User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );

        $url .= "&type=street";
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result ;
    }

    public static function getLocalityByLatLonDataGouv($lat, $lon){
        $url = "http://api-adresse.data.gouv.fr/reverse/?lon=".$lon."&lat=".$lat."&zoom=18&addressdetails=1" ;
        $json = file_get_contents($url);
        return $json ;
    }

    public static function getAddressByGeoDataGouv($lat, $lon){
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
                $nameCity = $address["addressLocality"] ;
            else
                $nameCity = null ;

            $resultNominatim = json_decode(self::getGeoByAddressNominatim($street, $cp, $nameCity, $country), true);
            $erreur = true ;
            if(!empty($resultNominatim[0])){
                $newGeo["geo"]["latitude"] = $resultNominatim[0]["lat"];
                $newGeo["geo"]["longitude"] = $resultNominatim[0]["lon"];
                if(empty($cp) && !empty($resultNominatim[0]["address"]["postcode"])){
                    $arraycp = explode(";", $resultNominatim[0]["address"]["postcode"]) ;
                    $cp = $arraycp[0] ;
                    $newAddress["postalCode"] = $cp ;
                }
                    

                $city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"],(empty($cp) ? null : $cp) );
                if(!empty($city)){
                    //foreach ($city as $key => $value){
                        $newAddress["codeInsee"] = $city["insee"];
                        $newAddress['addressCountry'] = $city["country"];
                        foreach ($city["postalCodes"] as $keyCp => $valueCp){
                            if($valueCp["postalCode"] == $cp){
                                $newAddress['addressLocality'] = $valueCp["name"];
                                $erreur = false ;
                            }
                        }
                }

            }

            if($erreur == true){
                $resultDataGouv = json_decode(self::getGeoByAddressDataGouv($street, $cp, $nameCity), true);
                if(!empty($resultDataGouv["features"])){
                    var_dump("resultDataGouv");
                    $newGeo["geo"]["latitude"] = $resultDataGouv["features"][0]["geometry"]["coordinates"][1];
                    $newGeo["geo"]["longitude"] = $resultDataGouv["features"][0]["geometry"]["location"][0];
                    $city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"],(empty($cp) ? null : $cp) );
                    
                    if(!empty($city)){
                        
                        $newAddress["codeInsee"] = $city["insee"];
                        $newAddress['addressCountry'] = $city["country"];
                        foreach ($city["postalCodes"] as $keyCp => $valueCp){
                            if($valueCp["postalCode"] == $cp){
                                $newAddress['addressLocality'] = $valueCp["name"];
                                $erreur = false ;
                            }
                        }
                    }
                }
            }

            if($erreur == true){
                $resultGoogle = json_decode(self::getGeoByAddressGoogleMap($street, $cp, $nameCity, $country), true);
                if(!empty($resultGoogle["results"])){
                    $newGeo["geo"]["latitude"] = $resultGoogle["results"][0]["geometry"]["location"]["lat"];
                    $newGeo["geo"]["longitude"] = $resultGoogle["results"][0]["geometry"]["location"]["lng"];
                    $city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"],(empty($cp) ? null : $cp) );
                    
                    if(!empty($city)){
                        
                        $newAddress["codeInsee"] = $city["insee"];
                        $newAddress['addressCountry'] = $city["country"];
                        foreach ($city["postalCodes"] as $keyCp => $valueCp){
                            if($valueCp["postalCode"] == $cp){
                                $newAddress['addressLocality'] = $valueCp["name"];
                                $erreur = false ;
                            }
                        }
                    }
                    /*else{
                            var_dump("Error");
                            var_dump($resultGoogle["results"]);
                        }*/
                }
            }

            if($erreur == true){
                $resultNominatim = json_decode(self::getGeoByAddressMinimunNominatim($cp, $nameCity, $country), true);
                if(!empty($resultNominatim[0])){
                    //var_dump("nominatim court");
                    //var_dump($resultNominatim);
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
                                    $erreur = false ;
                                }
                            }
                    }

                    /*else{
                        var_dump("Error");
                        var_dump($resultNominatim[0]);
                    }*/
                }
            }


            if($erreur == true){
                $resultGoogle = json_decode(self::getGeoByAddressMinimunGoogleMap($cp, $nameCity, $country), true);
                if(!empty($resultGoogle["results"])){
                    //var_dump("resultGoogle court");
                    //var_dump($resultGoogle);
                    $newGeo["geo"]["latitude"] = $resultGoogle["results"][0]["geometry"]["location"]["lat"];
                    $newGeo["geo"]["longitude"] = $resultGoogle["results"][0]["geometry"]["location"]["lng"];
                    $city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"],(empty($cp) ? null : $cp) );
                    if(!empty($city)){
                        $newAddress["codeInsee"] = $city["insee"];
                        $newAddress['addressCountry'] = $city["country"];
                        foreach ($city["postalCodes"] as $keyCp => $valueCp){
                            if($valueCp["postalCode"] == $cp){
                                $newAddress['addressLocality'] = $valueCp["name"];
                                $erreur = false ;
                            }
                        }
                    }
                    /*else{
                    var_dump("Error");
                    var_dump($resultGoogle["results"]);
                    }*/
                }
            }

            if($erreur == true){
                //var_dump("Error");
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
                        //    break;
                        //}
                    }
                }else{
                    throw new CTKException("N'est pas en France");
                }
            }else{
                $resultDataGouv = json_decode(self::getLocalityByLatLonNominatim($geo["latitude"], $geo["longitude"]), true);
            }
            

        } // Cas 4 : Il y a les 2
        else if(!empty($address) && !empty($geo)){
            $newGeo["geo"]["latitude"] = (empty($geo["latitude"])?"":$geo["latitude"]) ;
            $newGeo["geo"]["longitude"] = (empty($geo["longitude"])?"":$geo["longitude"]) ;

            if(!empty($address["streetAddress"]))
                $newAddress["streetAddress"] = $address["streetAddress"];          
            
            if(!empty($address["postalCode"])){
                $cp = $address["postalCode"] ;
                $newAddress["postalCode"] = $cp ;
            } 
            else
                $cp = null ;

            $city = SIG::getCityByLatLngGeoShape($newGeo["geo"]["latitude"], $newGeo["geo"]["longitude"],(empty($cp) ? null : $cp) );
            
            if(!empty($city)){
                $newAddress["codeInsee"] = $city["insee"];
                $newAddress['addressCountry'] = $city["country"];
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

        if(!empty($newGeo["geo"]["latitude"]) && !empty($newGeo["geo"]["longitude"])){

            $newGeo["geoPosition"] = array("type"=>"Point",
                                                "coordinates" =>
                                                    array(
                                                        floatval($newGeo["geo"]['longitude']),
                                                        floatval($newGeo["geo"]['latitude'])));
            $details["geo"] = $newGeo["geo"];
            $details["geoPosition"] = $newGeo["geoPosition"];
        }
        $details["address"] = $newAddress;
        //var_dump($details);
        return $details;
    } 


    public static function checkGeoShape(){
        $res = array();
        $where = array("geoShape" => array('$exists' => false) );
        $cities = PHDB::find(City::COLLECTION, $where);
        
        foreach ($cities as $key => $city) {
            
            $idCity = (String)$city["_id"];
            //var_dump($idCity);


            foreach ($city["postalCodes"] as $key => $value) {
                if($value["name"] == $city["alternateName"])
                    $cp = $value["postalCode"];
            }
        

            $resultNominatim = json_decode(Import::getGeoByAddressNominatim(null, $cp, $city["name"], null, true),true);

            $find = false;
            //var_dump($city["name"]);
            //var_dump($resultNominatim);
            if(!empty($resultNominatim)){
                foreach ($resultNominatim as $key => $cityNominatim){
                    //var_dump($cityNominatim);
                    if(!empty($cityNominatim["geojson"])){
                        //var_dump("etat 1");
                        if($find == false){
                             //var_dump("etat 2");
                            if($cityNominatim["geojson"]["type"] == "Polygon"){
                                var_dump("etat 3");
                                $city["geoShape"] = $cityNominatim["geojson"];
                                $find = true;
                            }else if($cityNominatim["geojson"]["type"] == "MultiPolygon"){
                                var_dump("etat 3");
                                $city["geoShape"] = $cityNominatim["geojson"];
                                $city["geoShape"]["type"] = "Polygon";
                                $find = true;
                            }
                        }
                    }
                }
            }else{

                $resultNominatim = json_decode(Import::getGeoByAddressNominatimGEOSHAPE($city["name"]),true);
                if(!empty($resultNominatim)){
                    foreach ($resultNominatim as $key => $cityNominatim){
                        if(!empty($cityNominatim["geojson"])){
                            if($find == false && $cityNominatim["address"]["country_code"] == "fr" ){
                                if($cityNominatim["geojson"]["type"] == "Polygon"){
                                    var_dump("etat 3");
                                    $city["geoShape"] = $cityNominatim["geojson"];
                                    $find = true;
                                }else if($cityNominatim["geojson"]["type"] == "MultiPolygon"){
                                    var_dump("etat 3");
                                    $city["geoShape"] = $cityNominatim["geojson"];
                                    $city["geoShape"]["type"] = "Polygon";
                                    $find = true;
                                }
                            }
                        }
                    }
                }
            }

            //$res[$city["name"]] = $find ;
            if($find == true){
                $res[$city["name"]] = PHDB::update(City::COLLECTION,
                                array("_id"=>new MongoId($idCity)),
                                array('$set' => $city),
                                array('upsert' => true));
            }
            

        }

        return $res ;

    }



    public static function getGeoByAddressNominatimGEOSHAPE( $city = null ){
        
        $url = "http://nominatim.openstreetmap.org/search?format=json&addressdetails=1&city=".str_replace(" ", "+", $city);
        $url .= "&polygon_geojson=1";
        //var_dump($url);
        $result = Import::getUrl($url);
        
        return $result;
    }


    public static function checkCedex($post){

        //var_dump($post);
        $res = array();
        
        foreach($post["params"]["file"] as $keyCSV => $lineCSV){
            $new = [];
            if(!empty($lineCSV[9]) && !empty($lineCSV[10]) ){
                $lat = $lineCSV[9];
                $lon = $lineCSV[10];
                if(strlen($lineCSV[1]) > 5 ){
                    $cp = substr($lineCSV[1],0,5);
                    $complement = substr($lineCSV[1],5,strlen($lineCSV[1]));
                    $city = SIG::getCityByLatLngGeoShape($lat, $lon, null);

                    if(!empty($city)){
                        $idCity = (String)$city["_id"];
                        $newCP["postalCode"] = $cp;
                        $newCP["complementPC"] = $complement;
                        $newCP["name"] = $lineCSV[2];
                        $newCP["geo"]["@type"] = "GeoCoordinates";
                        $newCP["geo"]["latitude"] = $lat;
                        $newCP["geo"]["longitude"] = $lon;
                        $newCP["geoPosition"] = array("type"=>"Point",
                                                                    "coordinates" =>
                                                                        array(
                                                                            floatval($lon),
                                                                            floatval($lat)));
                        $city["postalCodes"][] = $newCP;
                        PHDB::update(City::COLLECTION,
                                    array("_id"=>new MongoId($idCity)),
                                    array('$set' => $city),
                                    array('upsert' => true));
                    }

                }
                /*else{
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
                    }else{
                        $new["cp"] = $lineCSV[1];
                        $new["name"] = $lineCSV[2];
                        $res[] = $new ;
                    }
                }*/

            }
            
        }
        return $res ;

    }


    public static function nameCities( $name ){ 
        $newName = "";
        $search = array("-");
        $name = strip_tags (str_replace($search, " ", $name));
        $newName = strtoupper($name);
        return $newName;
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

   /* $arrayMicroformat = self::getMicroformat($post['idMicroformat']);

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

    public static function createSubFile(){
        if($type == "csv"){
            
            

            
        }


    }*/


    public static function getSource($idItem, $typeItem) {
        $source = array();
        $account = PHDB::findOneById($typeItem ,$idItem, array("source"));
        if(!empty($account["source"]))
            $source = $account["source"];
        return $source;
    }

    public static function checkSourceKeyInSource($key, $source) {
        $res = false ;
        
        if(!empty($source["key"])){
            if(is_array($source["key"])){
               foreach ($source["key"] as $k => $value) {
                    if($key == $value){
                        $res = true ;
                        break;
                    }
                } 
            }else if(is_string($source["key"]) && $key == $source["key"]){
                $res = true ;
            }  
        }
        return $res;
    }

    public static function addSourceKeyInSource($key, $source) {
        $res = array(   "result" => false, 
                        "source" => $source, 
                        "msg" => Yii::t("import","Le key est déjà dans la liste"));
        if(is_array($key)){
            foreach($key as $k => $value) {
                if(!self::checkSourceKeyInSource($value, $source))
                    $source["key"][] = $value;  
            }
            $res = array("result" => true, "source" => $source);
        }else if(is_string($key)){
            if(!self::checkSourceKeyInSource($key, $source))
                $source["key"][] = $key;
            $res = array("result" => true, "source" => $source); 
        }
        return $res;
    }

    public static function updateSourceKey($source, $idItem, $typeItem) {
        $res = array("result" => false, "msg" => Yii::t("import","La mise à jour a échoué."));
        if($typeItem == Person::COLLECTION)
            $res = Person::updatePersonField($idItem, "source", $source, Yii::app()->session["userId"]);
        else if($typeItem == Organization::COLLECTION)
            $res = Organization::updateOrganizationField($idItem, "source", $source, Yii::app()->session["userId"]);
        else if($typeItem == Person::COLLECTION)
            $res = Event::updateEventField($idItem, "source", $source, Yii::app()->session["userId"]);
        else if($typeItem == Person::COLLECTION)
            $res = Project::updateProjectField($idItem, "source", $source, Yii::app()->session["userId"]);
        return $res;
    }

    public static function addAndUpdateSourceKey($key, $idItem, $typeItem) {
        
        $source = self::getSource($idItem, $typeItem);
        $source["key"] = self::changeFormatSourceKeyInArray($source["key"]);
        $resAddSource = self::addSourceKeyInSource($key, $source);
        
        if($resAddSource["result"] == true){
            $res = self::updateSourceKey($resAddSource["source"], $idItem, $typeItem);
        }else
            $res = array("result" => false, "msg" => $resAddSource["msg"]);

        return $res;
    }

    public static function changeFormatSourceKeyInArray($keys) {
        return (is_string($keys))?array($keys):$keys;
    }
}

