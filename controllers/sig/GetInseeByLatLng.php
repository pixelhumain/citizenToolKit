<?php
class GetInseeByLatLngAction extends CAction
{
    public function run()
    {
        $city = SIG::getInseeByLatLng($_POST["lat"], $_POST["lng"],  $_POST["cp"]);
	    Rest::json( $city );
        Yii::app()->end();
    }
}