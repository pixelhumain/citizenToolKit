<?php

class AutocompleteMultiScopeAction extends CAction
{
    public function run( )
    {
        
        $type      = isset($_POST["type"])        ? $_POST["type"] : null;
        $scopeValue = isset($_POST["scopeValue"])   ? $_POST["scopeValue"] : null;

        if($type == null || $scopeValue == null) 
            return Rest::json( array("res"=>false, "msg" => "error with type or scopeValue" ));
       
        //Look for Insee code on city collection
        if($type == "city")     $where = array('$or'=> array(array("name" => new MongoRegex("/^".$scopeValue."/i")),
                                                             array("alternateName" => new MongoRegex("/^".$scopeValue."/i") ),
                                                             //array("postalCodes.name" => new MongoRegex("/^".$scopeValue."/i") )
                                                            ));
        
        //Look for postal code on city collection
        if($type == "cp")       $where = array("postalCodes.postalCode" =>new MongoRegex("/^".$scopeValue."/i"));
        
        
        if($type == "region")   $where = array('$or' => array(
                                                array("regionName" => new MongoRegex("/^".$scopeValue."/i")),
                                                array("region" => new MongoRegex("/^".$scopeValue."/i"))
                                                ));
        
        //var_dump($where); return;
        if($type != "dep" && $type != "region")
            $cities = PHDB::findAndSort( City::COLLECTION, $where, 
                                        array(), 15 ,
                                        array("insee", "postalCodes", "country", "name", "alternateName", "depName", "regionName"));
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
        
        return Rest::json( array("res"=>true, "cities" => $cities ));
       
    }
} 

