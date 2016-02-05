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
                $params = Import::parsingJSON($file, $post);
            else if($nameFile[count($nameFile)-1] == "csv")
                $params = Import::parsingCSV($post);
                //$params = Import::parsingCSV($file, $post);
        }

        return $params ;
    }


    public static function parsingJSON($file, $post) 
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

    }

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


    public static function parsingJSON2($file, $post) 
    {
        header('Content-Type: text/html; charset=UTF-8');
        if(isset($file['fileImport']))
        {
            $json = $post['file'];
            
            $nameFile = $post['nameFile'] ;
            $arrayNameFile = explode(".", $nameFile);
            
            $path = sys_get_temp_dir().'/filesImportData/' ;
            
            if(!file_exists($path))
                mkdir($path , 0775);

            $path = sys_get_temp_dir().'/filesImportData/'.$nameFile .'/';
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
                            "nameFile"=>$nameFile ,
                            "json_origine"=>$json,
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
                $pathMapping = FileHelper::arbreJson($value['mappingFields'], "", "");
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


	public static function parsingCSV($file, $post) 
    {
        header('Content-Type: text/html; charset=UTF-8');
        if(isset($file['fileImport']) && isset($post['separateurDonnees']) && isset($post['separateurTexte']) && isset($post['chooseCollection']))
        {
            $csv = new SplFileObject($file['fileImport']['tmp_name'], 'r');
            $csv->setFlags(SplFileObject::READ_CSV);
            
            $csv->setCsvControl($post['separateurDonnees'], $post['separateurTexte'], '"');
            /*$csvControl = $csv->getCsvControl();
            var_dump($csvControl);
            $csv->setCsvControl($csvControl[0], $csvControl[1]);*/

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
    }

    
    

    public static  function previewDataCSV($post) 
    {
        /**** new ****/
        $arrayJson = array();
        if(isset($post['infoCreateData']) && isset($post['idCollection']) && isset($post['subFile']) && isset($post['nameFile']))
        {
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
            
            //var_dump($post['infoCreateData']);
            //var_dump($arrayCSV);
            foreach ($arrayCSV as $keyCSV => $lineCSV) 
            {
                //var_dump($lineCSV);
                // On rejet la premier lignes qui correspond a l'en-tete, et les lignes qui seraient null
                if($i>0 && $lineCSV[0] != null)
                {
                    if (($i%500) == 0)
                    {
                        set_time_limit(30) ;
                    }
                    
                    foreach($post['infoCreateData']as $key => $objetInfoData) 
                    {
                        //var_dump($objetInfoData);
                        //$objetInfoData->idHeadCSV;
                        //$objetInfoData->valueLinkCollection;
                        
                        $valueData = $lineCSV[$objetInfoData['idHeadCSV']] ;
                        //var_dump($valueData);
                        
                        if(isset($valueData))
                        {
                            $paramsInfoCollection = array("_id"=>new MongoId($post['idCollection']));
                            $fieldsInfoCollection =  array("mappingFields.".$objetInfoData['valueLinkCollection']);

                            $infoCollection = Import::getMicroFormats($paramsInfoCollection);

                           
                            //var_dump($infoCollection) ; 
                            $mappingTypeData = explode(".", $post['idCollection'].".mappingFields.".$objetInfoData['valueLinkCollection']);


                            $typeData = FileHelper::get_value_json($infoCollection,$mappingTypeData);
                            

                            $mapping = explode(".", $objetInfoData['valueLinkCollection']);
                           
                            if(isset($jsonData[$mapping[0]]))
                            {
                                if(count($mapping) > 1)
                                { 
                                    $newmap = array_splice($mapping, 1);
                                    $jsonData[$mapping[0]] = FileHelper::create_json_with_father($newmap, $valueData, $jsonData[$mapping[0]], $typeData);
                                }
                                else
                                {
                                    $jsonData[$mapping[0]] = $valueData;
                                }
                                 $lineJsonArray[] = $valueData;
                            }
                            else
                            {
                                if(count($mapping) > 1)
                                { 
                                    $newmap = array_splice($mapping, 1);
                                    $jsonData[$mapping[0]] = FileHelper::create_json($newmap, $valueData, $typeData);
                                }
                                else
                                {
                                    $jsonData[$mapping[0]] = $valueData;
                                }
                                 $lineJsonArray[] = $valueData;
                            }
                           
                        }
                        
                    }
                    $newOrganization = Organization::newOrganizationFromImportData($jsonData, $post["creatorEmail"]);
                    $newOrganization["role"] = $post["role"];
                    $newOrganization["creator"] = $post["creatorID"];

                    
                    try{
                        $newOrganization2 = Organization::getAndCheckAdressOrganization($newOrganization);
                        //$newOrganization2 = Organization::getAndCheckNameOrganization($newOrganization, $arrayJson);
                        $arrayJson[] = Organization::getAndCheckOrganization($newOrganization2) ;
                    }
                    catch (CTKException $e){
                        $newOrganization["msgError"] = $e->getMessage();
                        $lineJsonArray[] = $e->getMessage();
                        $arrayJsonError[] = $newOrganization ;
                    }
                }
                $i++;
            }

            
            
            //var_dump($newOrganization);
            if(!isset($arrayJson))
                $arrayJson = array();

            if(!isset($arrayJsonError))
                $arrayJsonError = array();

            $rere = $arrayJson;
            foreach ($arrayJsonError as $key => $value) 
            {
                $rere[] = $value;
            }
            $test = Import::createArrayList($rere) ;

            $params = array("result" => true,
                            "jsonImport"=> json_encode($arrayJson),
                            "jsonError"=> json_encode($arrayJsonError),
                            "csvContenu" => $test);
        }
        else
        {
            $params = array("result" => false); 
        }
        return $params;
    }


    public static function previewDataJSON($post) 
    {
        $params = array("result" => false); 
        if(isset($post['infoCreateData']) && isset($post['jsonFile']))
        {

            //$pathFile =  sys_get_temp_dir().'/filesImportData/'.$post['nameFile'].'/'.$post['nameFile'].'.'.$post['typeFile'] ;
            //var_dump($pathFile); 

            //$file = file_get_contents(".".$pathFile, FILE_USE_INCLUDE_PATH);
            //var_dump($post['jsonFile']); 
            $jsonFile = json_decode($post['jsonFile'], true);
            $jsonFile2 = json_decode($jsonFile, true);
            //var_dump($jsonFile2);   
            foreach ($jsonFile2 as $keyJSON => $valueJSON) 
            {
                //var_dump($valueJSON);
                foreach($post['infoCreateData']as $key => $objetInfoData) 
                {

                    //$valueData = $valueJSON[$objetInfoData['idHeadCSV']] ;

                    //var_dump($objetInfoData['idHeadCSV']);
                    $cheminLien = explode(".", $objetInfoData['idHeadCSV']);
                    $valueData = FileHelper::get_value_json($valueJSON, $cheminLien);
                    //var_dump($valueData);
                    if(isset($valueData))
                    {
                        $paramsInfoCollection = array("_id"=>new MongoId($post['idCollection']));
                        $fieldsInfoCollection =  array("mappingFields.".$objetInfoData['valueLinkCollection']);

                        $infoCollection = Import::getMicroFormats($paramsInfoCollection);

                       
                        //var_dump($infoCollection) ; 
                        $mappingTypeData = explode(".", $post['idCollection'].".mappingFields.".$objetInfoData['valueLinkCollection']);


                        $typeData = FileHelper::get_value_json($infoCollection,$mappingTypeData);
                        //var_dump($mappingTypeData) ; 
                       
                        //var_dump($typeData) ; 

                        $mapping = explode(".", $objetInfoData['valueLinkCollection']);
                       
                        if(isset($jsonData[$mapping[0]]))
                        {
                            if(count($mapping) > 1)
                            { 
                                $newmap = array_splice($mapping, 1);
                                $jsonData[$mapping[0]] = FileHelper::create_json_with_father($newmap, $valueData, $jsonData[$mapping[0]], $typeData);
                            }
                            else
                            {
                                $jsonData[$mapping[0]] = $valueData;
                            }
                        }
                        else
                        {
                            if(count($mapping) > 1)
                            { 
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

                $newOrganization = Organization::newOrganizationFromImportData($jsonData, $post["creatorEmail"]);
                $newOrganization["role"] = $post["role"];
                $newOrganization["creator"] = $post["creatorID"];

                
                try{
                    $newOrganization2 = Organization::getAndCheckAdressOrganization($newOrganization);
                    //$newOrganization2 = Organization::getAndCheckNameOrganization($newOrganization, $arrayJson);
                    $arrayJson[] = Organization::getAndCheckOrganization($newOrganization2) ;
                }
                catch (CTKException $e){
                    $newOrganization["msgError"] = $e->getMessage();
                    $arrayJsonError[] = $newOrganization ;
                }
                //var_dump($valueJSON);
                   /* $cheminLien = explode(".", $idLien);
                    //var_dump($cheminLien);
                    $valueIdLien = FileHelper::get_value_json($json_array, $cheminLien) ;
                    //var_dump($valueIdLien);

                

                    $params = array('result'=> true,
                                    "jsonimport"=>FileHelper::indent_json(json_encode($jsonimport)),
                                    "jsonrejet"=>FileHelper::indent_json(json_encode($jsonrejet)),
                                    "lien"=>$post['lien']);*/
            }

            if(!isset($arrayJson))
                $arrayJson = array();

            if(!isset($arrayJsonError))
                $arrayJsonError = array();

            //var_dump($arrayJson);
            // var_dump($arrayJsonError);
            $params = array("result" => true,
                            "jsonImport"=> json_encode($arrayJson),
                            "jsonError"=> json_encode($arrayJsonError));
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



    public static function createArrayList($list) {
        $head2 = array("name", "msgError") ;
        $tableau = array();
        $tableau[] = $head2 ;
        foreach ($list as $keyList => $valueList){
            $element = array();
            foreach ($head2 as $keyHead => $valueHead){
                $map = explode(".", $valueHead);
                $element[] = FileHelper::get_value_json($valueList, $map);
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
}

