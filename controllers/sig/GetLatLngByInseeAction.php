<?php
class GetLatLngByInseeAction extends CAction
{
    public function run()
    {
	    $postalCode = isset($_POST["postalCode"]) ? $_POST["postalCode"] : null;
    	$position = SIG::getLatLngByInsee($_POST["insee"], $postalCode);
	    Rest::json( $position );
        Yii::app()->end();
    }
}