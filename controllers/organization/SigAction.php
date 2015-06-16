<?php

class SigAction extends CAction
{
	/**
	 * 
	 */
    public function run($id) {
    	$controller=$this->getController();
    	//get The organization Id
		if (empty($id)) {
		  throw new CTKException("The organization id is mandatory to retrieve the organization !");
		}

		$organization = Organization::getPublicData($id);

		
		$controller->pageTitle = ucfirst($controller->module->id)." - ".$controller->title;

		//Get this organizationEvent
		$events = array();
		if(isset($organization["links"]["events"])){
			foreach ($organization["links"]["events"] as $key => $value) {
				$event = Event::getPublicData($key);
				$events[$key] = $event;
			}
		}

		//récupère les données de certains type de membres (TODO : à compléter)
		if(isset($organization["links"]["members"])){
			foreach ($organization["links"]["members"] as $key => $value) {
					
				if( $value["type"] == 'organizations' ||
					$value["type"] == 'organization' ||
					$value["type"] == 'association'	 ||
					$value["type"] == 'NGO')			 { $publicData = Organization::getPublicData($key); }
					
				//if($value["type"] == 'citoyens')		 { $publicData = Person::getPublicData($key); }
				
<<<<<<< HEAD
				$addData = array("geo", "tags", "name", "typeIntervention", "public", "description");
=======
				$addData = array("geo", "tags", "name", "description","typeIntervention", "public"); //"typeIntervention", "public" GRANDDIR only
>>>>>>> 0b351b8ff1581e74a8097ec0eb03c0c0435513e2
				foreach($addData as $data) {
					if( !empty($publicData[$data]) )
						$organization["links"]["members"][$key][$data] = $publicData[$data];
				} 
<<<<<<< HEAD

				
			} //die(var_dump($organization));
=======
			}
>>>>>>> 0b351b8ff1581e74a8097ec0eb03c0c0435513e2
		}

		//Manage random Organization
		$organizationMembers = Organization::getMembersByOrganizationId($id, Organization::COLLECTION);
		$randomOrganizationId = array_rand($organizationMembers);
		$randomOrganization = Organization::getById($randomOrganizationId);

		$controller->render( "sig", array("randomOrganization" => $randomOrganization, 
										  "organization" => $organization, 
										  "events" => $events,
										  "members"=>$organizationMembers));
    }

}