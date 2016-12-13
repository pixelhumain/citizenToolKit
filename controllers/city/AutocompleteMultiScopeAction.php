<?php

class AutocompleteMultiScopeAction extends CAction
{
    public function run( )
    {
        
        $type       = isset($_POST["type"])         ? $_POST["type"] : null;
        $geoShape   = isset($_POST["geoShape"])     ? true : false;
        $scopeValue = isset($_POST["scopeValue"])   ? $_POST["scopeValue"] : null;
        $countryCode = isset($_POST["countryCode"])   ? $_POST["countryCode"] : null;

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

        if($countryCode != null)  $where = array_merge($where, array("country" => strtoupper($countryCode)));
        
        // if($type == "region")   $where = array('$or' => array(
        //                                         array("regionName" => new MongoRegex("/^".$scopeValue."/i")),
        //                                         array("region" => new MongoRegex("/^".$scopeValue."/i"))
        //                                         ));
        //var_dump($where); return;
        if($type != "dep" && $type != "region"){
            $att = array("insee", "postalCodes", "country", "name", "alternateName", "depName", "regionName");
            if($geoShape) $att[] =  "geoShape";
            //var_dump($where);
            $cities = PHDB::findAndSort( City::COLLECTION, $where, $att, 40, $att);
            if(empty($cities)){
                $resNominatim = json_decode(SIG::getGeoByAddressNominatim(null, null, $scopeValue, $countryCode, true, true),true);

                foreach ($resNominatim as $key => $value) {
                    $wikidata = json_decode(SIG::getWikidata($value["extratags"]["wikidata"]),true);
                    $valWiki = $wikidata["entities"][$value["extratags"]["wikidata"]]["claims"];
                    $newCities = array( "name" => $value["address"]["city"],
                                        "alternateName" => mb_strtoupper($value["address"]["city"]),
                                        "country" => $countryCode,
                                        "insee" => $valWiki[City::getInseeWikidataIDByCountry($countryCode)][0]["mainsnak"]["datavalue"]["value"]."*".$countryCode,
                                        "geo" => array( "@type"=>"GeoCoordinates", 
                                                        "latitude" => $value["lat"], 
                                                        "longitude" => $value["lon"]),

                                        "geoPosition" => array( "type"=>"Point", 
                                                                "coordinates" => array(
                                                                    floatval($value["lon"]), 
                                                                    floatval($value["lat"]))),
                                        //"geoShape" => $value["geojson"],
                                        "regionName" => $value["address"]["state"],
                                        "region" => null,
                                        "depName" => $value["address"]["county"],
                                        "dep" => null,
                                        "osmID" => $value["osm_id"],
                                        "wikidataID" => $value["extratags"]["wikidata"],
                                        "save" => true);

                    //P281 postalcode
                    foreach ($valWiki["P281"] as $key => $cp) {
                        $postalCodes[]  = array("name" => $value["address"]["city"],
                                                "postalCode" => $cp["mainsnak"]["datavalue"]["value"],
                                                "geo" => array( "@type"=>"GeoCoordinates", 
                                                                "latitude" => $value["lat"], 
                                                                "longitude" => $value["lon"]),
                                                "geoPosition" => array( "type"=>"Point", 
                                                                        "coordinates" => array(
                                                                            floatval($value["lon"]), 
                                                                            floatval($value["lat"]))));
                    }
                    $newCities["postalCodes"] = $postalCodes;
                    $newCities["geoShape"] = $value["geojson"];
                    $cities[] = $newCities;
                }
            }
        }
        else if($type == "dep"){
            $cities = array();
            foreach (OpenData::$dep as $key => $value) {
                if ( count( preg_grep ( '/'.$scopeValue.'/i', $value ) ) ) 
                    array_push($cities, $key);
            }
        }
        else if($type == "region"){
            $cities = array();
            foreach (OpenData::$region as $key => $value) {
                if ( count( preg_grep ( '/'.$scopeValue.'/i', $value ) ) ) 
                    array_push($cities, $key);
            }
        }
        //echo '<pre>';var_dump($cities);echo '</pre>'; return;
        
        return Rest::json( array("res"=>true, "cities" => $cities ));
       
    }


    
} 

