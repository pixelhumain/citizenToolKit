<?php
class GetLevelAction extends CAction
{
    public function run()
    {
    	$params = City::getLevelById($_POST["cityId"]);
    	Rest::json( $params );
	}
} ?>