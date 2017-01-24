<?php
class HomeAction extends CAction
{
	public function run() {
		
		// $countCitoyen 	= PHDB::count(Person::COLLECTION);
		// $countOrga 		= PHDB::count(Organization::COLLECTION);
		// $countProject 	= PHDB::count(Project::COLLECTION);
		// $countEvent 	= PHDB::count(Event::COLLECTION);

		// error_log("countCitoyen : " . $countCitoyen);

		$controller=$this->getController();
		$controller->layout = "//layouts/mainSearch";

        $controller->renderPartial( "home" , 
        	// array("countCitoyen" 	=> $countCitoyen,
        	// 	  "countOrga" 		=> $countOrga,
        	// 	  "countProject" 	=> $countProject,
        	// 	  "countEvent" 		=> $countEvent,
        		  
        		 ));
        
		//return Rest::json(array("result" => true, "list" => $search));
	}
}