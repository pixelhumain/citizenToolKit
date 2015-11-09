<?php
class GetInseeByLatLngAction extends CAction
{
    public function run()
    {
    	$city = SIG::getInseeByLatLngCp($_POST["latitude"], $_POST["longitude"],  (isset($_POST["cp"])) ? $_POST["cp"] : null);
	    Rest::json( $city );
        Yii::app()->end();
    }
}