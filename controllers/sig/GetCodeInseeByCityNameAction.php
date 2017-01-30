<?php
class GetCodeInseeByCityNameAction extends CAction
{
    public function run()
    {
    	//error_log(($_POST["cityName"]));
    	//error_log(utf8_encode($_POST["cityName"]));
    	$city = SIG::getCodeInseeByCityName($_POST["cityName"]);
	    Rest::json( $city );
        Yii::app()->end();
    }
}