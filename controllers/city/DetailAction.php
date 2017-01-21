<?php

class DetailAction extends CAction
{
	public function run( $insee, $postalCode=null, $zone=null )
    {
        $controller = $this->getController();
        //echo $insee;
        //get The person Id
        $id =  null;
        if (!empty($id)) 
            $id = Yii::app()->session["userId"];

        //$city = PHDB::findOne(City::COLLECTION, array( "insee" => $insee,  "postalCodes.postalCode" => $postalCode ) );

        if(empty($zone))
            $city = City::getCityByInseeCp($insee, $postalCode);
        else
            $city = City::getZone($insee, $zone);
        /*else
            $city = City::getWhere(array(insee => $insee),null, 1); */

        //si la city n'est pas trouvé par son code insee, on cherche avec le code postal
        //if($city == NULL) $city = PHDB::findOne(City::COLLECTION, array( "cp" => $insee ) );
        
        $city["typeSig"] = "city";
        // $city["cp"] = $postalCode;

        // $cityName = "";
        // foreach ($city["postalCodes"] as $key => $value) {
        //     if($value["postalCode"] == $postalCode){
        //         $city["name"] = $value["name"];
        //         $city["cp"] = $value["postalCode"];
        //         $city["geo"] = $value["geo"];
        //         $city["geoPosition"] = $value["geoPosition"];
        //     }
        // }

        //si la city n'a pas de position geo OU que les lat/lng ne sont pas définit (==0)
        if( !isset($city["geo"]) ||
            $city["geo"]["latitude"] == 0.00000000 || $city["geo"]["latitude"] == 0 ||
            $city["geo"]["longitude"] == 0.00000000 || $city["geo"]["longitude"] == 0 )
            {
                //on recherche la position de la ville via nominatim 
                //(limité à la France pour l'instant, pour être plus précis et trouver plus facilement)
                $url = 'http://nominatim.openstreetmap.org/search?';
                $params = http_build_query(array('postalcode' => $insee, 'country' => 'france', 'format' => 'json'));
                $appel_api = file_get_contents($url.$params);
                $resultats = json_decode($appel_api);
                
                //si nominatim a fournis une réponse, 
                //on récupère les coordonnées de la première réponse (même s'il y en a parfois plusieurs)
                if(isset($resultats) && isset($resultats[0])){
                    $city["geo"]["longitude"] = $resultats[0]->lon;
                    $city["geo"]["latitude"]  = $resultats[0]->lat;
                    $city["geo"]["boundingbox"]  = $resultats[0]->boundingbox;
                }
            }

        $person = ($id) ? Person::getPublicData($id) : null;
        //$person = PHDB::find(Person::COLLECTION, array( "address.codeInsee" => $insee ) );
        
        $contentKeyBase = Yii::app()->controller->id.".".Yii::app()->controller->action->id;
        $limit = array(Document::IMG_PROFIL => 1, Document::IMG_MEDIA => 5);
        $images = Document::getListDocumentsURLByContentKey($id, $contentKeyBase, Document::DOC_TYPE_IMAGE, $limit);

        $params = array( "person" => $person);
        $params['images'] = $images;
        $params["contentKeyBase"] = $contentKeyBase;
        $controller->sidebar1 = array(
          array('label' => "ACCUEIL", "key"=>"home","iconClass"=>"fa fa-home","href"=>"communecter/person/dashboard/id/".$id),
        );

        $controller->title = $city["name"]."'s City Dashboard";
        $controller->subTitle = (isset($city["description"])) ? $city["description"] : "";
        $controller->pageTitle = ucfirst($controller->module->id)." - Informations publiques de ".$controller->title;

        //Get Projects
        $whereElement = array( "address.codeInsee" => $insee ) ;
        if (!empty($postalCode))
            $whereElement["address.postalCode"] = $postalCode;

        $projectsBd = PHDB::find(Project::COLLECTION, $whereElement /*array( "address.codeInsee" => $insee )*/ );
        $projects = array();
        foreach ($projectsBd as $key => $project) {
            $project = Project::getPublicData((string)$project["_id"]);
            array_push($projects, $project);
        }
        
        //Get the Events
        $eventsBd = PHDB::find(Event::COLLECTION, $whereElement /*array( "address.codeInsee" => $insee )*/ );
        $events = array();
        foreach ($eventsBd as $key => $event) {
            $event = Event::getPublicData((string)$event["_id"]);
            array_push($events, $event);
        }
        
        $organizationsBd = PHDB::find(Organization::COLLECTION, $whereElement /*array( "address.codeInsee" => $insee )*/ );
        $organizations = array();
        foreach ($organizationsBd as $key => $orga) {
            $orga = Organization::getPublicData((string)$orga["_id"]);
            array_push($organizations, $orga);
        }
        
        
        $allPeople = array();
        $people = PHDB::find(Person::COLLECTION, $whereElement /*array( "address.codeInsee" => $insee )*/ );
        foreach ($people as $key => $onePerson) {
            $citoyen = Person::getPublicData( $key );
            array_push($allPeople, $citoyen);
        }
        $tags = PHDB::findOne( PHType::TYPE_LISTS,array("name"=>"tags"), array('list'));

        $params["tags"] = $tags;
        $params["organizations"] = $organizations;
        $params["projects"] = $projects;
        $params["events"] = $events;
        $params["people"] = $allPeople;
        $params["insee"] = $insee;
        $params["city"] = $city;

        $params["zones"] = City::getZones( $insee );
        $params["postalCodes"] = $city["postalCodes"];
        
        //$page = "detail2";

        if(!empty($postalCode))
            $params["cityGlobal"] = false;
        else
            $params["cityGlobal"] = true;
        $page = "detail";
        if(Yii::app()->request->isAjaxRequest)
            echo $controller->renderPartial($page,$params,true);
        else 
            $controller->render($page, $params );
    }
}
