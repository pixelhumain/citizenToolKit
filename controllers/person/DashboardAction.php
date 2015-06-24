<?php 
 /**
  * Display the dashboard of the person
  * @param String $id Not mandatory : if specify, look for the person with this Id. 
  * Else will get the id of the person logged
  * @return type
  */
class DashboardAction extends CAction
{
    public function run( $id=null )
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

	    $controller->title = ((isset($person["name"])) ? $person["name"] : "")."'s Dashboard";
	    $controller->subTitle = (isset($person["description"])) ? $person["description"] : "";
	    $controller->pageTitle = ucfirst($controller->module->id)." - Informations publiques de ".$controller->title;

	    //$controller->pageTitle = "Citoyens ".$controller->title." - ".$controller->subTitle;

	    if(isset($person["_id"]) && isset(Yii::app()->session["userId"]) && $person["_id"] != Yii::app()->session["userId"]){
			if(isset($person["_id"]) && isset(Yii::app()->session["userId"]) && Link::isConnected( Yii::app()->session['userId'] , PHType::TYPE_CITOYEN , (string)$person["_id"] , PHType::TYPE_CITOYEN ))
				$controller->toolbarMBZ = array("<li id='linkBtns'><a href='javascript:;' class='disconnectBtn text-red tooltips' data-name='".$person["name"]."' data-id='".$person["_id"]."' data-type='".Person::COLLECTION."' data-member-id='".Yii::app()->session["userId"]."' data-placement='top' data-original-title='Remove from my contact' ><i class='disconnectBtnIcon fa fa-unlink'></i>UNFOLLOW</a></li>" );
			else
				$controller->toolbarMBZ = array("<li id='linkBtns'><a href='javascript:;' class='connectBtn tooltips ' id='addKnowsRelation' data-placement='top' data-original-title='I know this person' ><i class=' connectBtnIcon fa fa-link '></i>FOLLOW</a></li>");
		}
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
	            if( $member['type'] == PHType::TYPE_CITOYEN )
	            {
	            	$citoyen = Person::getPublicData( $key );
	            	$profil = Document::getLastImageByKey($key, Person::COLLECTION, Document::IMG_PROFIL);
					if($profil !="")
						$citoyen["imagePath"]= $profil;
	            	array_push($people, $citoyen);
	            }
	    	}
	    	
	    }

	    $params["countries"] = OpenData::getCountriesList();
	    $params["listCodeOrga"] = Lists::get(array("organisationTypes"));
	   	$params["tags"] = $tags;
	    $params["organizations"] = $organizations;
	    $params["projects"] = $projects;
	    $params["events"] = $events;
	    $params["people"] = $people;

	    $controller->render("dashboard", $params );
    }
}
