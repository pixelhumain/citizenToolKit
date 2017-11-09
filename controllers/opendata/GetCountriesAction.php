<?php
/**
 * Retrieve all the Countries 
 * @return [json] {value : "theValue", text : "the Text"}
 */
class GetCountriesAction extends CAction
{
    public function run()
    {
        
        $countries = Zone::getListCountry();
        Rest::json($countries,false); 
        Yii::app()->end();
    }
}