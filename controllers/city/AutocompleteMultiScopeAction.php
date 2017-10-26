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

        if($type != "zone"){
            $cities = City::searchCity()
        }else if($type == "zone"){
            $att = array("name", "countryCode", "level");
            $where = array('$and'=> array(
                                        array("name" => new MongoRegex("/".$scopeValue."/i")), 
                                        array("countryCode" => strtoupper($countryCode)) ) );
            $cities = PHDB::findAndSort( Zone::COLLECTION, $where, $att, 40, $att);
        }
        
        return Rest::json( array("res"=>true, "cities" => $cities ));
       
    }


    
} 

