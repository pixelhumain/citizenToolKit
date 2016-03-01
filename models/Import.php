<?php
/*
	
 */
class Import
{ 
	const MICROFORMATS = "microformats";
    const ORGANIZATIONS = "organizations";
	

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


    public static function createJSON ($json, $nameFile, $path)
    {
        file_put_contents ($path.$nameFile , $json );
    }


    public static function parsingJSON2($post) 
    {
        header('Content-Type: text/html; charset=UTF-8');
        if(isset($post['nameFile']))
        {
            $json = $post['file'][0];

            $search = array("\t", "\n", "\r");
            $json = strip_tags (str_replace($search, " ", $json));
            
            $nameFile = $post['nameFile'] ;
            $arrayNameFile = explode(".", $nameFile);
            
            $path = sys_get_temp_dir().'/filesImportData/' ;
            
            if(!file_exists($path))
                mkdir($path , 0775);

            $path = sys_get_temp_dir().'/filesImportData/'.$nameFile .'/';
            if(!file_exists($path))
                mkdir($path , 0775);

            Import::createJSON($json, $nameFile, $path);
            $subFiles = scandir(sys_get_temp_dir()."/filesImportData/".$nameFile);
            
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

            $params = array("_id"=>new MongoId($post['chooseCollection']));
            $fields = array("mappingFields");
            $fieldsCollection = Import::getMicroFormats($params, $fields);
            $arrayPathMapping2 = array();
            foreach ($fieldsCollection as $key => $value) 
            {
                $pathMapping = ArrayHelper::getAllBranchsJSON($value['mappingFields'], "", "");
                $arrayPathMapping = explode(";",  $pathMapping);
                foreach ($arrayPathMapping as $keyPathMapping => $valuePathMapping) 
                {
                    
                    if(!empty($valuePathMapping))
                        $arrayPathMapping2[] =  $valuePathMapping;
                }
            }
            
            $params = array("createLink"=>true,
                            "typeFile" => "json",
                            "subFiles" => $subFiles,
                            "arbre"=>$listBrancheJson,
                            "nameFile"=>$nameFile ,
                            "json_origine"=>$json,
                            "jsonData"=>json_encode($json_objet),
                            "arrayPathMapping"=>$arrayPathMapping2,
                            "idCollection"=>$post['chooseCollection']);
            
        }
        else
        {
            $params = array("createLink"=>false);
        }
        return $params ;

    }

    public static function parsingCSV2($post) 
    {
        //var_dump($post);
        header('Content-Type: text/html; charset=UTF-8');

        if(isset($post['nameFile']) && isset($post['file']))
        {
            $path = sys_get_temp_dir().'/filesImportData/' ;
            if(!file_exists($path))
                mkdir($path , 0775);

            $path = sys_get_temp_dir().'/filesImportData/'.$post['nameFile'].'/';
            if(!file_exists($path))
                mkdir($path , 0775);

            $countLine = 0;
            $countFile = 1;
            $test = [];
            $line = [];

            foreach ($post['file'] as $key => $value) 
            {

                $arrayCSV[$key] = $value;
                if($key == 0)
                    $headerCSV = $value;
                
                if($countLine == 0 || $key == 0)
                    $arrayCSV[0] = $headerCSV;
                else
                    $arrayCSV[$countLine] = $value;


                if($countLine == 5000)
                {
                    $nameFile = $post['nameFile'].'_'.$countFile.'.csv' ;
                    Import::createCSV($arrayCSV, $nameFile, $path);
                    $countLine = 0;
                    $countFile++;
                    $arrayCSV = array();
                }
                else
                    $countLine++;

            }
            $nameFile = $post['nameFile'].'_'.$countFile.'.csv' ;
            Import::createCSV($arrayCSV, $nameFile, $path);
            
            $subFiles = scandir(sys_get_temp_dir()."/filesImportData/".$post['nameFile']);


            $params = array("_id"=>new MongoId($post['chooseCollection']));
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
            
            $params = array("createLink"=>true,
                            "typeFile" => "csv",
                            "arrayCSV" => $post['file'],
                            "subFiles" => $subFiles,
                            "nameFile"=>$post['nameFile'],
                            "arrayPathMapping"=>$arrayPathMapping2,
                            "idCollection"=>$post['chooseCollection']);
        }
        else
        {
            $params = array("createLink"=>false);  
        }

        return $params ;
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

        if(isset($post['infoCreateData']) && isset($post['idCollection']) && isset($post['subFile']) && isset($post['nameFile'])){

            $paramsInfoCollection = array("_id"=>new MongoId($post['idCollection']));
            $infoCollection = Import::getMicroFormats($paramsInfoCollection);


            $pathSubFile =  sys_get_temp_dir().'/filesImportData/'.$post['nameFile'].'/'.$post['subFile'] ;
            $arrayCSV = new SplFileObject($pathSubFile, 'r');
            $arrayCSV->setFlags(SplFileObject::READ_CSV);
            $arrayCSV->setCsvControl(',', '"', '"');

            $i = 0 ;
            while (!$arrayCSV->eof() && $i == 0) {
                $arrayHeadCSV = $arrayCSV->fgetcsv() ;
                $i++;
            }
           
            $i = 0 ;
            
            foreach ($arrayCSV as $keyCSV => $lineCSV){
                $jsonData = array();
                if($i>0 && $lineCSV[0] != null){
                    
                    if (($i%500) == 0)
                        set_time_limit(30) ;
                    
                    foreach($post['infoCreateData']as $key => $objetInfoData){
                        
                        $valueData = $lineCSV[$objetInfoData['idHeadCSV']] ;
                        
                        if(isset($valueData)) {
                            $mappingTypeData = explode(".", $post['idCollection'].".mappingFields.".$objetInfoData['valueLinkCollection']);
                            $typeData = ArrayHelper::getValueJson($infoCollection,$mappingTypeData);
                            $mapping = explode(".", $objetInfoData['valueLinkCollection']);
                           
                            if(isset($jsonData[$mapping[0]]))
                            {
                                if(count($mapping) > 1){ 
                                    $newmap = array_splice($mapping, 1);
                                    $jsonData[$mapping[0]] = FileHelper::create_json_with_father($newmap, $valueData, $jsonData[$mapping[0]], $typeData);
                                }
                                else{
                                    $jsonData[$mapping[0]] = $valueData;
                                }
                            }
                            else
                            {
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

                    $entite = Import::checkData($infoCollection[$post['idCollection']]["key"], $jsonData, $post);

                    if(empty($entite["msgError"]))
                        $arrayJson[] = $entite ;
                    else
                        $arrayJsonError[] = $entite ; 
                    
                }
                $i++;
            }

            
            
            //var_dump($newOrganization);
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
                            "listEntite" => $listEntite);
        }
        else
        {
            $params = array("result" => false); 
        }
        return $params;
    }


    public static function checkData($keyCollection, $data, $post){
        $res = array() ;
        if($keyCollection == "Organizations"){
            try{    
                $newOrganization = Organization::newOrganizationFromImportData($data, $post["creatorEmail"]);
                $newOrganization["role"] = $post["role"];
                $newOrganization["creator"] = $post["creatorID"];
                $newOrganization2 = Organization::getAndCheckAdressOrganization($newOrganization);
                $res = Organization::getAndCheckOrganization($newOrganization2) ;
            }
            catch (CTKException $e){
                if(empty($newOrganization))
                    $newOrganization = $data;
                $newOrganization["msgError"] = $e->getMessage();
                $res = $newOrganization ;
            }
        } else if($keyCollection == "Projets"){
            try{
                //$data["creator"] = $post["creatorID"];
                //var_dump($data);
                $data["source"]['sourceKey'] = "patapouf";
                $newProject = Project::createProjectFromImportData($data);
                $newProject2 = Project::getQuestionAnwser($newProject);
                $res = Project::getAndCheckProjectFromImportData($newProject2, $post["creatorID"]);
            }
            catch(CTKException $e){
                if(empty($newProject))
                    $newProject = $data;

                $newProject["msgError"] = $e->getMessage();
                
                $res = $newProject ;
            }
        }

        return $res ;
    }

    public static function previewDataJSON($post) 
    {
        $params = array("result" => false); 
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

            foreach ($jsonFile2 as $keyJSON => $valueJSON){
                //var_dump("--------------------------------------");
                $jsonData = array();
                foreach($post['infoCreateData']as $key => $objetInfoData){
                    
                    $cheminLien = explode(".", $objetInfoData['idHeadCSV']);
                    //var_dump($objetInfoData['idHeadCSV']);
                    
                    $valueData = ArrayHelper::getValueJson($valueJSON, $cheminLien);
                    //var_dump($valueData);
                    if(!empty($valueData)){
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
                
                //var_dump($jsonData);

                $entite = Import::checkData($infoCollection[$post['idCollection']]["key"], $jsonData, $post);


                if(empty($entite["msgError"]))
                    $arrayJson[] = $entite ;
                else
                    $arrayJsonError[] = $entite ; 

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
            
            if(empty($listEntite))
                $listEntite = Import::createArrayList($listEntite) ;

            $params = array("result" => true,
                            "jsonImport"=> json_encode($arrayJson),
                            "jsonError"=> json_encode($arrayJsonError),
                            "listEntite"=> $listEntite);
        }

        return $params;

    }
    
    public static function previewData($post) 
    {
        $params = array("createLink"=>false);
        //var_dump($post['typeFile']);

        if(isset($post['typeFile']))
        {
            if($post['typeFile'] == "json" || $post['typeFile'] == "js")
                $params = Import::previewDataJSON($post);
            else if($post['typeFile'] == "csv")
                $params = Import::previewDataCSV($post);
        }

        return $params ;

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
        $head2 = array("name", "msgError") ;
        $tableau = array();
        $tableau[] = $head2 ;
        //var_dump($list);
        foreach ($list as $keyList => $valueList){
            $element = array();
            foreach ($head2 as $keyHead => $valueHead){
                $map = explode(".", $valueHead);
                //$element[] = ArrayHelper::getValueJson($valueList, $map);
            }
            $tableau[] = $element ;
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






    public static function addDataInDb($post)
    {
        $jsonString = $post["file"];
        $typeEntity = $post["chooseEntity"];

        if(substr($jsonString, 0,1) == "{")
            $jsonArray[] = json_decode($jsonString, true) ;
        else
            $jsonArray = json_decode($jsonString, true) ;

        if(isset($jsonArray)){
            $resData =  array();
            foreach ($jsonArray as $key => $value){
                try{
                    $res = Project::insertProjetFromImportData($value, $post['creatorID'],Person::COLLECTION) ; 

                    if($res["result"] == true){
                        $projet["name"] =  $value["name"];
                        
                        $projet["info"] = "Success" ;
                    }else{
                        $projet["name"] =  $value["name"];
                        
                        $projet["info"] = "Error" ;
                    }
                    $resData[] = $projet;
                }
                catch (CTKException $e){
                    $projet["name"] =  $value["name"];
                    
                    $projet["info"] = $e->getMessage();
                    $resData[] = $projet;
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
}

