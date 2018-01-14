<?php
/**
 * Retrieve all the Countries 
 * @return [json] {value : "theValue", text : "the Text"}
 */
class GetCountriesAction extends CAction
{
    public function run($hasCity = null)
    {
    	if(!is_bool($hasCity))
    		$hasCity = ($hasCity == "true" ? true : null);
        $countries = Zone::getListCountry($hasCity);
        Rest::json($countries,false); 
        Yii::app()->end();
    }
}