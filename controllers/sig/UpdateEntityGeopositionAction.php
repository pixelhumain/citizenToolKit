<?php
class UpdateEntityGeopositionAction extends CAction
{
    public function run()
    {
    	SIG::updateEntityGeoposition($_POST["entityType"], $_POST["entityId"], $_POST["latitude"], $_POST["longitude"]);
    }
}