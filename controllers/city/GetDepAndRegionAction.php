<?php
class GetDepAndRegionAction extends CAction
{
    public function run()
    {
    	$params = City::getDepAndRegionByInsee($_POST["insee"]);
    	Rest::json( $params );
	}
} ?>