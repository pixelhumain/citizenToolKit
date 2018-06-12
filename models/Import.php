<?php
/*
    
 */
class Import
{ 
    const MAPPINGS = "mappings";

    // Prend en paramètre le fichier, fonction sur le fichier
    public static function parsing($post) {
        $params = array("result"=>false); //Vérifie si le fichier est vide
        // Prends en charge les fichiers JSON
        if($post['typeFile'] == "json" || $post['typeFile'] == "js" || $post['typeFile'] == "geojson")
            $params = self::parsingJSON($post);
        //Prends en charge les fichier CSV
        else if($post['typeFile'] == "csv")
            $params = self::parsingCSV($post); 
        return $params ;
    }

    //Partie concernant les fichiers CSV
    public static function parsingCSV($post) {
        
        //Permet de choisir quelle type d'élèment l'utilisateur veut transmettre en stockant dans un tableau
        $attributesElt = ArrayHelper::getAllPathJson(file_get_contents("../../modules/co2/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH));
        
        //Dans le cas EST selectionné.
        if($post['idMapping'] != "-1"){

            //Récupère id de l'élèment choisi.
            $where = array("_id" => new MongoId($post['idMapping']));
            $fields = array("fields");
            $mapping = self::getMappings($where, $fields);
            $find = PHDB::findOne(self::MAPPINGS,$where);
            //Remplace les "_dot_" par des "."
            $mapping[$post['idMapping']]["fields"] = Mapping::replaceByRealDot($mapping[$post['idMapping']]["fields"]);
            $arrayMapping = $mapping[$post['idMapping']]["fields"];

            $params = array("result"=>true,
            "attributesElt"=>$attributesElt,
            "arrayMapping"=>$arrayMapping,
            "typeFile"=>$post['typeFile'],
            "idMapping" =>$post['idMapping'],
            "nameUpdate" => $find['name']);
        }
        else
        {
        //Tableau vide.
            $arrayMapping = array();
            $params = array("result"=>true,
            "attributesElt"=>$attributesElt,
            "arrayMapping"=>$arrayMapping,
            "typeFile"=>$post['typeFile'],
            "idMapping" =>$post['idMapping']);
        }
        //Stock l'élèment choisi ainsi que le fichier qui va avec.

        return $params ;
    }

    public static function parsingJSON($post){
        //Type du json et le format du json.
        header('Content-Type: text/html; charset=UTF-8');
        
        //On crée un tableau qui a une boolean result à faux
        $params = array("result"=>false);
        if(isset($post['file'])) {
            
            $json = $post['file'][0];
            /*(empty($post["path"]) ? $post['file'][0] : $post['file'][0][$post["path"]]);
            if (isset($post['path'])) {
                 $json = $post['file'][0][$post['path']][0];
            }*/ 

            //Si l'élément a été selectionné.
            if($post['idMapping'] != "-1"){
                
                $where = array("_id" => new MongoId($post['idMapping']));
                $fields = array("fields");
                $mapping = self::getMappings($where, $fields);
                $mapping[$post['idMapping']]["fields"] = Mapping::replaceByRealDot($mapping[$post['idMapping']]["fields"]);
                $arrayMapping = $mapping[$post['idMapping']]["fields"];
                $find = PHDB::findOne(self::MAPPINGS,$where);
            }

            //Tableau vide.
            else
                $arrayMapping = array();

            //Si path n'est pas vide, permet ed décoder et de encoder le json dans un format lisible pour le php
            if(!empty($post["path"])){
                $json = json_decode($json,true);
                $json = $json[$post["path"]];
                $json = json_encode($json);
            }

            if(substr($json, 0,1) == "{")
                //Tableau qui prend en paramètre le json
                $arbre = ArrayHelper::getAllPathJson($json); 
            else{
                //Tableau vide
                $arbre = array();

                //parcour le tableau json pour le stocker dans el tableau arbre 
                foreach (json_decode($json,true) as $key => $value) {
                    $arbre = ArrayHelper::getAllPathJson(json_encode($value), $arbre); 
                }
            }

            //Permet de stocker les différents élèments

            $attributesElt = ArrayHelper::getAllPathJson(file_get_contents("../../modules/co2/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH));
            
            //Stock dans une variable que le resultat sera vrai, l'élèment choisir, la conversion du json en php et le fichier json
            if($post['idMapping'] != "-1"){
            $params = array("result"=>true,
                        "attributesElt"=>$attributesElt,
                        "arrayMapping"=>$arrayMapping,
                        "arbre"=>$arbre,
                        "typeFile"=>$post['typeFile'],
                        "idMapping" =>$post['idMapping'],
                        "nameUpdate" => $find['name']);          
            }
            else
            {
                $params = array("result"=>true,
                "attributesElt"=>$attributesElt,
                "arrayMapping"=>$arrayMapping,
                "arbre"=>$arbre,
                "typeFile"=>$post['typeFile'],
                "idMapping" =>$post['idMapping']);       
            }
        }
            return $params ;
    }

    //Info sûr : prend en entrée un tableau et un champ qui est NULL.
    public static function getMappings($where=array(),$fields=null){

        $allMappings = PHDB::find(self::MAPPINGS, $where, $fields);
        $allMappings = self::getStandartMapping($allMappings);
        return $allMappings;
    }

   
    //Test le fichiers en stockant les élèments dans un tableau si c'est correct ou non.
    public static  function previewData($post){
        $params = array("result"=>false);
        $elements = array();
        $saveCities = array();
        $nb = 0 ;
        $elementsWarnings = array();
        // $post = les différents champs remplis.
        if(!empty($post['infoCreateData']) && !empty($post['file']) && !empty($post['nameFile']) && !empty($post['typeFile'])){
            $mapping = json_decode(file_get_contents("../../modules/co2/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH), true);
            $attributesElt = ArrayHelper::getAllPathJson(file_get_contents("../../modules/co2/data/import/".Element::getControlerByCollection($post["typeElement"]).".json", FILE_USE_INCLUDE_PATH));
            
            //Pour les fichiers de type csv.
            if($post['typeFile'] == "csv"){
                $file = $post['file'];
                $headFile = $file[0];
                unset($file[0]);
            }
            elseif ((!isset($post['pathObject'])) || ($post['pathObject'] == "")) {
                $file = json_decode($post['file'][0], true);
            }
            else {
                $file = json_decode($post['file'][0], true);
                $file = @$file[$post["pathObject"]];
            }
            
            if(@$file)
            foreach ($file as $keyFile => $valueFile){ //Parcours les informations par COLONNE du fichier
                $nb++;
                //if(!empty($valueFile)){
                    $element = array();     //Tableau vide
                    foreach ($post['infoCreateData'] as $key => $value) { //Parcours les informations PAR LIGNE
                        $valueData = null;

                        if($post['typeFile'] == "csv" && in_array($value["idHeadCSV"], $headFile)){
                            $idValueFile = array_search($value["idHeadCSV"], $headFile);
                            $valFile =  (!empty($valueFile[$idValueFile])?$valueFile[$idValueFile]:null);
                        }else if ($post['typeFile'] == "json"){
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
                    //Tableau double entrée.
                    $element['source']['insertOrign'] = "import";
                    if(!empty($post['key'])){
                        //Tableau triple entrée.
                        $element['source']['keys'][] = $post['key'];
                        $element['source']['key'] = $post['key'];
                    }

                    //Vérifie si élément ce trouve bien dans la BDD par rapport à ce que l'utilisateur a fournis comme information.
                    $element = self::checkElement($element, $post['typeElement']);
                    if(!empty($element["saveCities"])){
                    	$saveCities[] = $element["saveCities"];
                    	unset($element["saveCities"]);
                    }

                    //Vérifie si on a choisi une personne et on lui lance une invitation avec un msg.
                    if($post['typeElement'] == Person::COLLECTION){
                        $element['msgInvite'] = $post['msgInvite'];
                        $element['nameInvitor'] = $post['nameInvitor'];
                    }

                    //On vérifie s'il n'y a pas d'erreur, en cas d'erreur on stock les élèments dans la partie erreur
                    if(!empty($element['msgError']) || ($post['warnings'] == "false" && !empty($element['warnings'])))
                        $elementsWarnings[] = $element;
                    else
                        $elements[] = $element;
                //}
            }
            
            //Tableau qui prend un résultat en true, les elements corrects, incorrect et les différents liste d'élèment que l'utilisateur aura sélectionné.
            $params = array("result"=>true,
                            "elements"=>json_encode(json_decode(json_encode($elements),true)),
                            "elementsWarnings"=>json_encode(json_decode(json_encode($elementsWarnings),true)),
                            "saveCities"=>json_encode(json_decode(json_encode($saveCities),true)),
                            "listEntite"=>self::createArrayList(array_merge($elements, $elementsWarnings)));
        }

        return $params;
    }  

    //Crée un tableau pour les élèments qui sera afficher dans les Données.
    public static function createArrayList($list) {
        $head = array("name", "address", "warnings", "msgError") ;
        $tableau = array($head);
        foreach ($list as $keyList => $valueList){
            $ligne = array();
            //Si la valueList est vide alors on la liste vide sinon on mets valueList['name'];
            $ligne[] = (empty($valueList["name"])? "" : $valueList["name"]);

            //On complete l'adresse en vérifie si tous les champs ont bien était noté, dans le cas si ne c'est pas le cas, on le laisse vide.
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

    //Les messages d'erreurs
    public static function getMessagesWarnings($warnings){
        $msg = "";
        foreach ($warnings as $key => $codeWarning) {
            if($msg != "")
                $msg .= "<br/>";
            $msg .= Yii::t("import",$codeWarning);
        }
        return $msg;
    }
//Vérifie la provenance des informations du fichiers s'ils sont valides ou non, mais aussi pour vérifier si elles sont remplis ou non.
    public static function checkElement($element, $typeElement){
        $result = array("result" => true);

        //Si la variable element géo est vide alors on la laisse vide sinon on la complète
        $geo = (empty($element['geo']) ? null : $element['geo']);

        if($typeElement != Person::COLLECTION){
            //SI ADRESSE EST VIDE ALORS ON LA LAISSE VIDE, SINON LA COMPLETE, PAREIL POUR GEO.
            $address = (empty($element['address']) ? null : $element['address']);
            $geo = (empty($element['geo']) ? null : $element['geo']);

            if(!empty($address) && !empty($address["addressCountry"])  && !empty($address["postalCode"]) && strtoupper($address["addressCountry"]) == "FR" && strlen($address["postalCode"]) == 4 )
                $address["postalCode"] = '0'.$address["postalCode"];

                //Permet de vérifier la géolocalisation d'une adresse.
                $detailsLocality = self::getAndCheckAddressForEntity($address, $geo) ;
            
                //Dans le cas où l'adresse de localisation est valide
            if($detailsLocality["result"] == true){
				$element["address"] = $detailsLocality["address"] ;
				$element["geo"] = $detailsLocality["geo"] ;
				$element["geoPosition"] = $detailsLocality["geoPosition"] ;
				if(!empty($detailsLocality["saveCities"]))
               		$saveCities = $detailsLocality["saveCities"] ; 
            }
        }

        //Vérifie si les évent et lié à un projet, on vérifie s'il y a une date de début de et fin
        if($typeElement == Event::COLLECTION || $typeElement == Project::COLLECTION){
            date_default_timezone_set('UTC');
            if(!empty($element['startDate']))
                $element['startDate'] = date('Y-m-d H:i:s', strtotime($element['startDate']));
            
            if(!empty($element['endDate']))
                $element['endDate'] = date('Y-m-d H:i:s', strtotime($element['endDate']));
        }

        //On vérifie s'il y a des tags
        if(!empty($element["tags"]))
            $element["tags"] = self::checkTag($element["tags"]);
        
        

        //On vérifie le type d'organisation
		if($typeElement == Organization::COLLECTION && !empty($element["type"]))
        	$element["type"] = Organization::translateType($element["type"]);

        //Vérification des réseaux sociaux
        if(!empty($element["facebook"]))
            $element["socialNetwork"]["facebook"] = $element["facebook"];

        if ($element['source']['keys'][0] !== "convert_datagouv" && $element['source']['keys'][0] !== "convert_osm" && $element['source']['keys'][0] !== "convert_ods" && $element['source']['keys'][0] !== "convert_wiki" && $element['source']['keys'][0] !== "convert_datanova" && $element['source']['keys'][0] !== "convert_poleemploi" && $element['source']['keys'][0] !== "convert_educ_etab" && $element['source']['keys'][0] !== "convert_educ_membre" && $element['source']['keys'][0] !== "convert_educ_ecole" && $element['source']['keys'][0] !== "convert_educ_struct" && $element['source']['keys'][0] !== "convert_valueflows" && $element['source']['keys'][0] !== "convert_organcity") {
            $element = self::getWarnings($element, $typeElement, true) ;
        }

        $resDataValidator = DataValidator::validate(Element::getControlerByCollection($typeElement), $element, true);

        //Génération d'un message d'erreur.
        if($resDataValidator["result"] != true){
            //$element["msgError"] = ((empty($resDataValidator["msg"]->getMessage()))?$resDataValidator["msg"]:$resDataValidator["msg"]->getMessage());
            $element["msgError"] = $resDataValidator["msg"];
        }

        if(!empty($saveCities))
            $element["saveCities"] = $saveCities ; 
        
        return $element;
    }

    //Fonction qui vérifie les tags s'il existe dans la base de donnée et les stocks dans un tableau.
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

    //On vérifie si l'adresse est bien valide.
    public static function getAddressConform($city, $address){
        //Création d'un tableau
        $newA = array(
                '@type' => 'PostalAddress',
                'addressCountry' =>  $city["country"]);

        //Si la ville possèed un id, on complète le tableau.
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

        //Vérifie l'existant de l'adresse et du code postale, la rue...
        if( !empty($address["postalCode"]) &&  !empty($city["postalCodes"]) ) {
            foreach ($city["postalCodes"] as $keyCp => $valueCp){
                if($valueCp["postalCode"] == $address["postalCode"]){
                    $newA['addressLocality'] = $valueCp["name"];
                    $newA['postalCode'] = $address["postalCode"];
                }
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

        return $newA ;
    }

    //Permet de vérifie la géolocalisation d'une adresse depuis diverse base de donnée externe.

    public static function getLatLonBySIG($address){
        //Si l'adresse "rue adresse" est vide alors on la laisse vide sinon on l'a complète
        $street = (empty($address["streetAddress"])?null:$address["streetAddress"]);
        //Si l'adresse "code postal" est vide alors on la laisse vide sinon on l'a complète
    	$cp = (empty($address["postalCode"])?null:$address["postalCode"]);
        $geo = array();
        //Vérifie l'adresse depuis la base de donnée du gouvernement (leurs géolocalisation)
    	$resultDataGouv = ( ( !empty($address["addressCountry"]) && $address["addressCountry"] == "FR" ) ? ( empty($cp) ? null : json_decode(SIG::getGeoByAddressDataGouv($street, $cp, $address["addressLocality"]), true) ) : null ) ;


		if(!empty($resultDataGouv["features"])){
			$geo["lat"] = strval($resultDataGouv["features"][0]["geometry"]["coordinates"][1]);
			$geo["lon"] = strval($resultDataGouv["features"][0]["geometry"]["coordinates"][0]);
		}else{
            //Vérifie l'adresse depuis la base de donnée Nominatim (leurs géolocalisation)
			$resultNominatim = json_decode(SIG::getGeoByAddressNominatim($street, $cp, $address["addressLocality"], $address["addressCountry"]), true);
			if(!empty($resultNominatim[0])){
				$geo["lat"] = $resultNominatim[0]["lat"];
				$geo["lon"] = $resultNominatim[0]["lon"];
			}else{
                //Vérifie l'adresse depuis la base de donnée Google (leurs géolocalisation)
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
        //var_dump($address);
		if( !empty($address["addressLocality"]) && !empty($address["addressCountry"]) ){

            //Convertis les caractères spéciaux d'une adresse pour pouvoir établir une recherche.
            $regexCity = Search::accentToRegex(strtolower($address["addressLocality"]));
            //var_dump($regexCity);

            //On stock les résultats dans un tableau tout en réalisant les requêtes
			$where = array('$or'=> 
						array(  
							array("name" => new MongoRegex("/^".$regexCity."/i")),
							array("alternateName" => new MongoRegex("/^".$regexCity."/i")),
							array("postalCodes.name" => new MongoRegex("/^".$regexCity."/i"))
						) );
			$where = array('$and' => array($where, array("country" => strtoupper($address["addressCountry"])) ) );

			if( !empty($address["postalCode"]) ){
				$where = array('$and' => array($where, array("postalCodes.postalCode" => $address["postalCode"]) ) );
			}
            $fields = array("name", "geo", "country", "level1", "level1Name","level2", "level2Name","level3", "level3Name","level4", "level4Name", "osmID", "postalCode", "insee");

			$city = PHDB::findOne(City::COLLECTION, $where, $fields);

			if(!empty($city)){
                $resGeo = self::getLatLonBySIG($address);
                //Si resGeo est vide alors on le complete avec les données de city[geo][latitude] sinon on remplit avec les données de resGeo[lat]
                $lat = ( empty($resGeo["lat"]) ? $city["geo"]["latitude"] : $resGeo["lat"] );
                //Si resReo est vide alors on le complete avec les données de city[geo][longitude] sinon on remplit avec les données de resGeo[long]
				$lon = ( empty($resGeo["lon"]) ? $city["geo"]["longitude"] : $resGeo["lon"] );
				
				$newA = self::getAddressConform($city, $address);
				$newGeo = SIG::getFormatGeo($lat, $lon);
				$newGeoPosition = SIG::getFormatGeoPosition($lat, $lon);
				$result = true;
			} else {
                //Décode l'adresse d'un fichier json
				$resNominatim = json_decode(SIG::getGeoByAddressNominatim(null, null, $address["addressLocality"], trim($address["addressCountry"]), false, true),true);
				if(!empty($resNominatim)){

					$typeCities = array("city", "village", "town") ;
                    //On recherche les informations que contient resNominatim
                    foreach ($resNominatim as $keyN=> $valueN) {
                        $break = false ;
                        //On vérifie le type de ville
						foreach ($typeCities as $keyT=> $valueT) {
							if( !empty($valueN["address"][$valueT]) && 
								$address["addressCountry"] == strtoupper(@$valueN["address"]["country_code"])) {   

                                    //On sauvegarde les informations de l'adresse
									$saveCities = array( 	"name" => $valueN["address"][$valueT],
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

                                    //On sauvegarde si l'adresse est valide
                                    $newA = self::getAddressConform($saveCities, $address);
                                    $result = true;
                                    break;
    						}
                        }

                        //Si le résultat est bon, on arrête les recherches
                        if($result == true)
                            break;
					}
				}
			}

        }
        //Si la géo possèe une valeur en latitude & longitude
        else if(!empty($geo["latitude"]) && !empty($geo["longitude"])){
            //Si la latitude correspond bien à une valeur numérique, alors on l'a récupère au format chaîne, sinon on la récupère tel quelle
            $lat = ( is_numeric($geo["latitude"]) ? strval($geo["latitude"]) : $geo["latitude"] ) ;
            //Si la longitude correpond bien à une valeur numérique, alors on l'a récupère au format chaîne, sinon la récupère tel quelle.
			$lon = ( is_numeric($geo["longitude"]) ? strval($geo["longitude"]) : $geo["longitude"] ) ;

			$city = SIG::getCityByLatLngGeoShape( $lat, $lon, null, (!empty($address["addressCountry"]) ? $address["addressCountry"] : null ) ) ;
            //var_dump($city);
            if(!empty($city)){
                //vérifie l'adresse.
                $newA = self::getAddressConform($city, $address);
                //stock dans un tableau les informations pour la geolocalisation
                $newGeo = SIG::getFormatGeo($lat, $lon);
                //stock la position dans un tableau pour la geolocalisation
				$newGeoPosition = SIG::getFormatGeoPosition($lat, $lon);
				$result = true;
            }else{

            	$resNominatim = json_decode(SIG::getLocalityByLatLonNominatim($lat, $lon),true, true);
            	if(!empty($resNominatim)){
                    //vérifie si l'adresse est dans une ville ou un village.
                    $nameCity = self::getCityNameInNominatim($resNominatim["address"]);
                    //Si on s'est la provenance de l'adresse.
                    if(!empty($nameCity)){
                        
                        //Sauvegarde les données dans un tableau.
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
                        //$saveCities = $newA;
                        $result = true;
                    }
				}
            }		
        } // fin elseif
        //Stock les informations concernant la geo de l'adresse et l'adresse
        $res = array(	"result" => $result,
                    //Si l'adresse est vide alors on l'a laisse vide sinon on l'a complète
                        "address" => ( empty($newA) ? null : $newA),
                    //SI la geo est vide alors on l'a laisse vide sinon on l'a complète
                        "geo" => ( empty($newGeo) ? null : $newGeo),
                    //Si la geoPosition est vide alors on laisse vide sinon on l'a complète
                        "geoPosition" => ( empty($newGeoPosition) ? null : $newGeoPosition),
                    //Si on n'a sauvegardé les données de l'adresse vide, on l'a laisse vide, sinon on l'a complète
						"saveCities" => ( empty($saveCities) ? null : $saveCities) );

		return $res ;
	}

    //Parcours une adresse pour savoir qu'elle type d'adresse s'est (ville or village).
    public static function getCityNameInNominatim($address){
        $typeCities = array("city", "village", "town") ;
        foreach ($typeCities as $key => $value) {
            if(!empty($address[$value]))
                return $address[$value] ;
        }
        return null;
    }

    //Gestion des erreurs trouvé
    public static function getWarnings($element, $typeElement, $import = null){
        $warnings = array();

        if(empty($element['name']))
            $warnings[] = "201" ;

        if(empty($element['email']) && $typeElement == Person::COLLECTION)
            $warnings[] = "203" ;

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

    //Lien pour remplir automatiquement le tableau
    public static function initMappings(){
        $mappings = json_decode(file_get_contents("../../modules/co2/data/import/mappings.json", FILE_USE_INCLUDE_PATH), true);
        foreach ($mappings as $key => $value) {
            PHDB::insert( Import::MAPPINGS, $value );
        }
    }

    //Ajoute à la BDD.
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
                        //Vérifie l'existent dans la BDD.
                        $exist = City::alreadyExists($value, $typeElement);
                        //Dans le cas ou une information n'est pas trouvé dans la BDD, on la rajoute
						if(!$exist["result"]) {
							$res = City::insert($value, Yii::app()->session["userId"]);
							$element["name"] = $value["name"];
							$element["info"] = $res["msg"];
						}else{
							$element["name"] = $exist["city"]["name"];
							$element["info"] = "La ville existes déjà";
						}

                    }else{

                        //Ajoute l'adresse à la base de donnée, vérifie l'existent à la BDD.
						if( !empty( $value["address"] ) ) {
							$good = true ;
							if(!empty($value["address"]["osmID"])){
								$city = City::getByOsmId($value["address"]["osmID"]);

								if(!empty($city)){
									$value["address"] = self::getAddressConform($city, $value["address"]);
									$resGeo = self::getLatLonBySIG($value["address"]);
									$value["geo"] = SIG::getFormatGeo($resGeo["lat"], $resGeo["lon"]);
									$value["geoPosition"] = SIG::getFormatGeoPosition($resGeo["lat"], $resGeo["lon"]);
								}
								else{
									$good = false ;
									$element["name"] = $exist["element"]["name"];
									$element["info"] = "La commune n'existe pas, penser a l'ajouter avants"; 
								}
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

    

    public static function setCedex($post){        
        //SI le type de fichier est csv
        if($post['typeFile'] == "csv"){
			$file = $post['file'];
			//$headFile = $file[0];
			unset($file[0]);
        }
        //Sinon c'est du json
        else{
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

    //Vérifie les erreurs
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

    //Les différents paramètres que l'utilisateur va nous renseigner pour convertir son fichiers
    public static function getParams($file, $type, $url) {

        $param = array();

        //Type
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

        //Lien
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

        //Affiche la carte ainsi que le position
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

    //Retourne une liste de lien pour la mape
    public static function getDatalayersUmap($url){

        $url_map = $url;

        //Lire et decode le lien
        $umap_data = file_get_contents($url_map);
        $umap_data = json_decode($umap_data, true);

        //Préparation des tableaux pour stockers
        $list_id = array();
        $list_url_datalayers = array();

        //Parcours du tableau
        foreach ($umap_data["properties"]["datalayers"] as $key => $value) {

            $url_datalayers = 'http://umap.openstreetmap.fr/en/datalayer/'.$value['id'];
            array_push($list_url_datalayers, $url_datalayers);

        }

        return $list_url_datalayers;

    }

    //Le résultat de la maps ?
    public static function getUmapResult($url, $param) {

        //Lit la chaîne url entièrement
        $umap_data = file_get_contents($url);

        $list_url_data = self::getDatalayersUmap($url);

        $param['nameFile'] = $url;
        $res = array();

        //Boucle qui lis le contenu de la liste
        foreach ($list_url_data as $keyDatalayer => $valueDatalayer) {  

            $datalayers_data = file_get_contents($valueDatalayer);
            $param['file'][0] = $datalayers_data;

            //Test le résultat et le stock
            $result = self::previewData($param);

            $result = $result['elements'];

            //Si c'est pas vide alors on le rajoute dans le tableau en le décodant
            if (!empty(json_decode($result))) {
                array_push($res, json_decode($result));
            }
        }

        return $res;
    }

    //Permet de récupérée les différents "lien" pré enregistré dans la base de donnée par rapport à leur
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

