<?php
class GetInseeByLatLngAction extends CAction
{
    public function run()
    {
    	//var_dump($_POST);
    	//error_log("getInsee OK");
        $city = SIG::getInseeByLatLngCp($_POST["latitude"], 
    									$_POST["longitude"],  
    									isset($_POST["cp"]) ? $_POST["cp"] : null);
        //var_dump($city);
	    Rest::json( $city );
        Yii::app()->end();
    }
}