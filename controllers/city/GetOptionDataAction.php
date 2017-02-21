<?php

class GetOptionDataAction extends CAction
{
    public function run($insee,$typeData="population")
    {
       
        $where = array("insee"=>$_GET['insee'], $typeData => array( '$exists' => 1 ));
        $fields = array($typeData);
        $option = City::getWhereData($where, $fields);
        
        $name_id = $typeData ;

        $chaine = "" ;
        foreach ($option as $key => $value) 
        {
            foreach ($value as $k => $v) 
            {
                if($k == $typeData)
                {
                    $chaine = CityOpenData::listOption($v, $chaine, true, $name_id);
                }   
            }
        }
        $params["listOption"] = $chaine ;
        
        Rest::json($params);
    }
}