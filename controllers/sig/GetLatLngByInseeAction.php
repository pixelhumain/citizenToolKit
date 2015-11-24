<?php
class GetLatLngByInseeAction extends CAction
{
    public function run()
    {
    	$position = SIG::getLatLngByInsee($_POST["insee"]);
	    Rest::json( $position );
        Yii::app()->end();
    }
}