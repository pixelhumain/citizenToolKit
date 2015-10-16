<?php

class OpenDataAction extends CAction
{
    public function run($insee,$typeData="population", $type=null)
    {
    	
        $controller=$this->getController();

        $where = array("_id"=>new MongoId(Yii::app()->session["userId"]), "preferences.dashboards.city/opendata" => array( '$exists' => 1 ));
        $result = PHDB::findOne("citoyens", $where);
        //var_dump($result);
        /*if(empty($result))
        {
            $pod1["title"] = "Population de 2011" ;
            $pod1["url"] = "http://127.0.0.1/ph/communecter/city/graphcity/insee/97414/typeData/population/typeGraph/multibart/typeZone/commune/optionData/0=.2011.total" ;
            $city["city/opendata"] = $pod1 ;
            $dashboards["dashboards"] = $city ;
            $preferences["preferences"] = $dashboards ;

            $res = PHDB::update("citoyens",
                            array("_id"=>new MongoId(Yii::app()->session["userId"])), 
                            array('$set' => $preferences));
           // var_dump($res);
        }**/

        $city = PHDB::findOne( City::COLLECTION , array( "insee" => $insee ) );
        $name = (isset($city["name"])) ? $city["name"] : "";
        $controller->title = ( (!empty($name)) ? $name : "City : ".$insee)."'s Directory";
        $controller->subTitle = (isset($city["description"])) ? $city["description"] : "";
        $controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

        $params["insee"] = $insee;
        $params["city"] = $city;
        $page = "openData";
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
        else 
        $controller->render($page, $params );
    }
}