<?php 
 /**
  * Display the dashboard of the person
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class IndexAction extends CAction
{
    public function run( $insee=null )
    {
        $controller = $this->getController();

	    //get The person Id
	    if (empty($id)) {
	        if (empty(Yii::app()->session["userId"])) {
	            $controller->redirect(Yii::app()->homeUrl);
	        } else {
	            $id = Yii::app()->session["userId"];
	        }
	    }

	    $city = PHDB::findOne(City::COLLECTION, array( "insee" => $insee ) );
	    //si la city n'est pas trouvé par son code insee, on cherche avec le code postal
	    if($city == NULL) $city = PHDB::findOne(City::COLLECTION, array( "cp" => $insee ) );

	    //si la city n'a pas de position geo OU que les lat/lng ne sont pas définit (==0)
	    if( !isset($city["geo"]) ||
	    	$city["geo"]["latitude"] == 0.00000000 || $city["geo"]["latitude"] == 0 ||
	    	$city["geo"]["longitude"] == 0.00000000 || $city["geo"]["longitude"] == 0)
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

	    $person = Person::getPublicData($id);
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
	    $projects = array();
	    if(isset($person["links"]["projects"])){
	    	foreach ($person["links"]["projects"] as $key => $value) {
	  			$project = Project::getPublicData($key);
	  			array_push($projects, $project);
	  		}
	    }

	    
	    //Get the Events
	  	$events = Authorisation::listEventsIamAdminOf($id);
	  	$eventsAttending = Event::listEventAttending($id);
	  	foreach ($eventsAttending as $key => $value) {
	  		$eventId = (string)$value["_id"];
	  		if(!isset($events[$eventId])){
	  			$events[$eventId] = $value;
	  		}
	  	}
	  	$tags = PHDB::findOne( PHType::TYPE_LISTS,array("name"=>"tags"), array('list'));
	    //TODO - SBAR : Pour le dashboard person, affiche t-on les événements des associations dont je suis memebre ?
	  	//Get the organization where i am member of;
	  	$organizations = array();
	    if( isset($person["links"]) && isset($person["links"]["memberOf"])) {
	    	
	        foreach ($person["links"]["memberOf"] as $key => $member) {
	            $organization;
	            if( $member['type'] == Organization::COLLECTION )
	            {
	                $organization = Organization::getPublicData( $key );
	                $profil = Document::getLastImageByKey($key, Organization::COLLECTION, Document::IMG_PROFIL);
					if($profil !="")
						$organization["imagePath"]= $profil;
	                array_push($organizations, $organization );
	            }
	       
	         	if(isset($organization["links"]["events"])){
		  			foreach ($organization["links"]["events"] as $keyEv => $valueEv) {
		  				$event = Event::getPublicData($keyEv);
		  				$events[$keyEv] = $event;	
		  			}
		  			
		  		}
	        }        
	        //$randomOrganizationId = array_rand($subOrganizationIds);
	        //$randomOrganization = Organization::getById( $subOrganizationIds[$randomOrganizationId] );
	        //$params["randomOrganization"] = $randomOrganization;
	        
	    }
	    $people = array();
	    if( isset($person["links"]) && isset($person["links"]["knows"])) {
	    	foreach ($person["links"]["knows"] as $key => $member) {
	    		$citoyen;
	            if( $member['type'] == Person::COLLECTION )
	            {
	            	$citoyen = Person::getPublicData( $key );
	            	$profil = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_PROFIL);
					if($profil !="")
						$citoyen["imagePath"]= $profil;
	            	array_push($people, $citoyen);
	            }
	    	}
	    }

	   	$params["tags"] = $tags;
	    $params["organizations"] = $organizations;
	    $params["projects"] = $projects;
	    $params["events"] = $events;
	    $params["people"] = $people;
	    $params["insee"] = $insee;
	    $params["city"] = $city;

	    $controller->render("dashboard", $params );
    }
}
