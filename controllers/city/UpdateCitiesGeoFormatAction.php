<?php
class UpdateCitiesGeoFormatAction extends CAction
{
    public function run()
    {
    	$success = City::updateGeoPositions();
	    Rest::json( $success );
        Yii::app()->end();
    }
}