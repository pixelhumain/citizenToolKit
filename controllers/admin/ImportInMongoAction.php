<?php

class ImportInMongoAction extends CAction
{
    public function run()
    {

    	$paramsInfoCollection = array("_id"=>new MongoId($_POST['idCollection']));
        $infoCollection = Import::getMicroFormats($paramsInfoCollection);

       	$params = array();
        //var_dump($infoCollection[$_POST['idCollection']]['key']) ; 
        if($infoCollection[$_POST['idCollection']]['key'] == "Organizations")
        	$params = Import::importOrganizationsInMongo($_POST);
        if($infoCollection[$_POST['idCollection']]['key'] == "Projets")
        	$params = Import::importProjectsInMongo($_POST);

        return Rest::json($params);
    }
}

?>