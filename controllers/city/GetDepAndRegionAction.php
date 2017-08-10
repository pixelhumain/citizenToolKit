<?php
class GetDepAndRegionAction extends CAction
{
    public function run()
    {
    	$params = City::getDepAndRegionByInsee($_POST["key"]);
    	Rest::json( $params );
	}
} ?>