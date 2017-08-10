<?php

class AutocompleteMultiScopeAction extends CAction
{
    public function run( )
    {
        
        $type       = isset($_POST["type"])         ? $_POST["type"] : null;
        $geoShape   = isset($_POST["geoShape"])     ? true : false;
        $scopeValue = isset($_POST["scopeValue"])   ? $_POST["scopeValue"] : null;
        $countryCode = isset($_POST["countryCode"])   ? $_POST["countryCode"] : null;
        $formInMap = isset($_POST["formInMap"])   ? $_POST["formInMap"] : null;

        if($type == null || $scopeValue == null) 
            return Rest::json( array("res"=>false, "msg" => "error with type or scopeValue" ));
       
        //Look for Insee code on city collection
        if($type == "city") {
            $scopeValue = str_replace(array(
                        '/', '-', '*', '+', '?', '|',
                        '(', ')', '[', ']', '{', '}', '\\', " "), ".", $scopeValue);
            $where = array('$or'=> 
                            array(array("name" => new MongoRegex("/".$scopeValue."/i")),
                            array("alternateName" => new MongoRegex("/".$scopeValue."/i")),
                    ));
        }
        //Look for postal code on city collection
        if($type == "cp")       $where = array("postalCodes.postalCode" =>new MongoRegex("/^".$scopeValue."/i"));
        //if($countryCode != null) $where = array("country" => strtoupper($countryCode));

        if($countryCode != null && !empty($where))  $where = array_merge($where, array("country" => strtoupper($countryCode)));

        if($type == "locality") {
            $scopeValue = str_replace(array(
                        '/', '-', '*', '+', '?', '|',
                        '(', ')', '[', ']', '{', '}', '\\', " "), ".", trim($scopeValue));
            $where = array('$or'=> 
                            array(  array("name" => new MongoRegex("/".$scopeValue."/i")),
                                    array("alternateName" => new MongoRegex("/".$scopeValue."/i")),
                                    array("postalCodes.postalCode" => new MongoRegex("/^".$scopeValue."/i")),
                                    array("postalCodes.name" => new MongoRegex("/^".$scopeValue."/i")),
                            )
                        );
            $where = array('$and'=> array($where, array("country" => strtoupper($countryCode)) )
                     );
            //var_dump($where);
        }

        
        
        // if($type == "region")   $where = array('$or' => array(
        //                                         array("regionName" => new MongoRegex("/^".$scopeValue."/i")),
        //                                         array("region" => new MongoRegex("/^".$scopeValue."/i"))
        //                                         ));
        //var_dump($where); return;
        if(/*$type != "dep" && $type != "region" &&*/ $type != "zone"){
            $att = array("name", "alternateName", "country", "key", "postalCodes", "insee", "depName", "regionName");
            if($geoShape) $att[] =  "geoShape";
            //var_dump($where);
            $cities = PHDB::findAndSort( City::COLLECTION, $where, $att, 40, $att);
            if(empty($cities) && !empty($formInMap)){
                $countryCode = mb_convert_encoding($countryCode, "ASCII");
                if(strlen($countryCode) > 2 ){
                   $countryCode = substr($countryCode, 0, 2);
                }
                $countryCode = mb_convert_encoding($countryCode, "UTF-8");
                $resNominatim = json_decode(SIG::getGeoByAddressNominatim(null, null, $scopeValue, trim($countryCode), true, true),true);
                //var_dump($resNominatim);
                if(!empty($resNominatim)){
                    //var_dump($resNominatim);
                    foreach (@$resNominatim as $key => $value) {
                        $typeCities = array("city", "village", "town") ;
                        foreach ($typeCities as $keyType => $valueType) {
                            if( !empty($value["address"][$valueType]) 
                                && $countryCode == strtoupper(@$value["address"]["country_code"])) {

                                $wikidata = (empty($value["extratags"]["wikidata"]) ? null : $value["extratags"]["wikidata"]);
                                //var_dump($value["osm_id"]);
                                $newCities = array( "name" => $value["address"][$valueType],
                                                    "alternateName" => mb_strtoupper($value["address"][$valueType]),
                                                    "country" => $countryCode,
                                                    "geo" => array( "@type"=>"GeoCoordinates", 
                                                                    "latitude" => $value["lat"], 
                                                                    "longitude" => $value["lon"]),

                                                    "geoPosition" => array( "type"=>"Point",
                                                                            "float"=>true, 
                                                                            "coordinates" => array(
                                                                                floatval($value["lon"]), 
                                                                                floatval($value["lat"]))),
                                                    "regionName" => (empty($value["address"]["state"]) ? null : $value["address"]["state"] ),
                                                    "region" => null,
                                                    "depName" => (empty($value["address"]["county"]) ? null : $value["address"]["county"] ),
                                                    "dep" => null,
                                                    "osmID" => $value["osm_id"],
                                                   
                                                    "save" => true);
                                if(!empty($wikidata))
                                    $newCities = City::getCitiesWithWikiData($wikidata, $newCities);
                                

                                if(empty($newCities["insee"]))
                                    $newCities["insee"] = $value["osm_id"]."*".$countryCode;

                                if(empty($newCities["postalCodes"]))
                                    $newCities["postalCodes"] = array();

                                if(empty($newCities["geoShape"]))
                                    $newCities["geoShape"] = $value["geojson"];

                              
                                if(City::checkCitySimply($newCities))
                                    $cities[] = $newCities;
                                
                                
                            }
                        } 

                        
                        
                    }
                }
                
            }
        }else if($type == "zone"){
            $att = array("name", "countryCode", "key", "level");
            $where = array('$and'=> array(
                                        array("name" => new MongoRegex("/".$scopeValue."/i")), 
                                        array("countryCode" => strtoupper($countryCode)) ) );
            $cities = PHDB::findAndSort( Zone::COLLECTION, $where, $att, 40, $att);
        }
        /*else if($type == "dep"){
            $att = array("name", "countryCode", "key");
            $where = array("name" => new MongoRegex("/".$scopeValue."/i"));
            $cities = PHDB::findAndSort( Zone::COLLECTION, $where, $att, 40, $att);
        }
        else if($type == "region"){
            $att = array("name", "countryCode", "key");
            $where = array("name" => new MongoRegex("/".$scopeValue."/i"));
            $cities = PHDB::findAndSort( Zone::COLLECTION, $where, $att, 40, $att);
        }*/
        //echo '<pre>';var_dump($cities);echo '</pre>'; return;
        
        return Rest::json( array("res"=>true, "cities" => $cities ));
       
    }


    
} 

