<?php 
class CO2 {

    public static function getThemeParams($domainName=null){

    	$domainName = @$domainName ? $domainName : Yii::app()->params["CO2DomainName"];
    	$params = array("CO2"=>array(
    						"title" => "CO2",

    						"pages" => 
    							array(
    							    "#co2.index"=>
    								 	array("redirect"=>"social"),
    								  
    								"#co2.social"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "social", 
						                      "subdomainName" => "Recherche",
						                      "icon" => "search", 
						                      "mainTitle" => "Moteur de recherche <span class='letter-red'>territorial</span>",
						                      "placeholderMainSearch" => "Rechercher une page ..."),

    								"#co2.freedom"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "freedom", 
						                      "subdomainName" => "Live",
						                      "icon" => "rss", 
						                      "mainTitle" => "Un fil d'actu <span class='letter-red'>commun</span>",
						                      "placeholderMainSearch" => "rechercher dans le fil d'actualités"),

    								"#co2.agenda"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "agenda", 
						                      "subdomainName" => "agenda",
						                      "icon" => "calendar", 
						                      "mainTitle" => "L'agenda<span class='letter-red'>CO</span>mmun",
						                      "placeholderMainSearch" => "rechercher un événement ..."),

    								"#co2.power"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "power", 
						                      "subdomainName" => "power",
						                      "icon" => "hand-rock-o", 
						                      "mainTitle" => "Un bien <span class='letter-red'>CO</span>mmun dédié à l'intelligence <span class='letter-red'>CO</span>llective",
						                      "placeholderMainSearch" => "rechercher parmis les propositions ..."),

    								"#co2.page.type"=>
    								  	array("inMenu" => false, 
						                      "useHeader" => false, 
						                      "subdomain" => "page.type", 
						                      "subdomainName" => "page",
						                      "icon" => "", 
						                      "mainTitle" => "Le réseau social à effet de sert",
						                      "placeholderMainSearch" => "rechercher parmis les membres du réseau ..."),			  

    						)
    					),


    					"kgougle"=>array(
    						"title" => "kgougle",

    						"pages" => 
    							array(
    								"#co2.index"=>
	    								 	array("redirect"=>"web"),
    								  
    								"#co2.web"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "web", 
						                      "subdomainName" => "web",
						                      "icon" => "search", 
						                      "mainTitle" => "Le moteur de recherche des Cagous",
						                      "placeholderMainSearch" => "rechercher sur le web Calédonien ..."),

    								"#co2.referencement"=>
    								  	array("inMenu" => false, 
						                      "useHeader" => true, 
						                      "subdomain" => "referencement", 
						                      "subdomainName" => "referencement",
						                      "icon" => "search", 
						                      "mainTitle" => "Référencer un site",
						                      "placeholderMainSearch" => "rechercher sur le web Calédonien ..."),

    								"#co2.live"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "live", 
						                      "subdomainName" => "live",
						                      "icon" => "newspaper-o", 
						                      "mainTitle" => "Toute l'actu du pays",
						                      "placeholderMainSearch" => "rechercher dans l'actu ..."),

    								"#co2.social"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "social", 
						                      "subdomainName" => "social",
						                      "icon" => "user-circle-o", 
						                      "mainTitle" => "Le réseau social Calédonien",
						                      "placeholderMainSearch" => "rechercher une page ..."),

    								"#co2.freedom"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "freedom", 
						                      "subdomainName" => "freedom",
						                      "icon" => "comments", 
						                      "mainTitle" => "Toutes vos annonces en direct",
						                      "placeholderMainSearch" => "rechercher parmis les annonces  ..."),

    								"#co2.agenda"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "agenda", 
						                      "subdomainName" => "agenda",
						                      "icon" => "calendar", 
						                      "mainTitle" => "L'agenda collaboratif des Calédoniens",
						                      "placeholderMainSearch" => "rechercher un événement  ..."),

    								"#co2.power"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "power", 
						                      "subdomainName" => "power",
						                      "icon" => "hand-rock-o", 
						                      "mainTitle" => "L'espace collaboratif des Calédoniens.",
						                      "placeholderMainSearch" => "rechercher parmis les propositions"),

    								"#co2.page.type"=>
    								  	array("inMenu" => false, 
						                      "useHeader" => false, 
						                      "subdomain" => "page.type", 
						                      "subdomainName" => "page",
						                      "icon" => "", 
						                      "mainTitle" => "Le réseau social Calédonien",
						                      "placeholderMainSearch" => "rechercher parmis les membres du réseau ..."),
    							) 								  

    					));

    	if(isset($params[$domainName])) return $params[$domainName]; 
    	else return false;
    }


    public static function getCitiesNewCaledonia(){
    	$query = array("country"=>"NC", "name"=>array('$in'=>array("Noumea", "Dumbea", "Paita", "Mont-Dore")));
    	$citiesGN = PHDB::find(City::COLLECTION, $query);

    	$query = array("country"=>"NC", "depName"=>"Province Sud", "name"=>array('$nin'=>array("Noumea", "Dumbea", "Paita", "Mont-Dore")));
    	$citiesS = PHDB::find(City::COLLECTION, $query);

    	$query = array("country"=>"NC", "depName"=>"Province Nord");
    	$citiesN = PHDB::find(City::COLLECTION, $query);

    	$query = array("country"=>"NC", "depName"=>"Province Des Iles");
    	$citiesI = PHDB::find(City::COLLECTION, $query);

    	$cities = array("GN"=>$citiesGN, 
    					"Sud"=>$citiesS, 
    					"Nord"=>$citiesN, 
    					"Iles"=>$citiesI);
    	return $cities;
    }


    public static function getAnnounceCategories(){
    	$cat = array("Technologie" => 
    					array("TV / Vidéo", "Informatique", "Tablettes", "Téléphonie", "Appareils photos", "Appareil audio"), 
					 
					 "Immobilier" => 
					 	array("Maison", "Appartement", "Terrain", "Parking", "Bureaux"), 
					 
					 "Véhicules" => 
					 	array(	"Voiture", 
					 			"SUV", 
					 			"4x4", 
					 			"Moto", 
					 			"Scooter", 
					 			"Bateau", 
					 			"Voiturette", 
					 			"Vélos",
					 			"Équipement véhicule",
					 			"Équipement 2 roues",
					 			"Équipement bateau",
					 			"Équipement vélo"), 

					 "Maison" => 
						 array(	 "Electroménager",
								 "Mobilier", 
								 "Équipement bébé", 
			    				 "Animaux", 
			    				 "Divers"),
					 "Loisirs" => 
						 array(	 "Sports", 
						 		 "Instrument musique", 
						 		 "Sonorisation", 
						 		 "CD / DVD",
								 "Jouet", 
			    				 "Jeux de société", 
			    				 "Livres / BD", 
			    				 "Collections", 
			    				 "Bricolages", 
			    				 "Jardinage", 
			    				 "Art / Déco", 
			    				 "Modélisme", 
			    				 "Puériculture", 
			    				 "Animaux", 
			    				 "Divers"),
	    			"Mode" => 
						 array(	 "Vêtements", 
								 "Chaussures", 
			    				 "Accessoires", 
			    				 "Montres", 
			    				 "Bijoux", 
			    				 )
	    			);

    	return $cat;
    }

    public static function getJobsCategories(){
    	$cat = array("Technologie" => 
    					array("TV / Vidéo", "Informatique", "Tablettes", "Téléphonie", "Appareils photos", "Appareil audio"), 
					 
					 "Logement" => 
					 	array("Vente", "Location", "Collocation"), 
					 
					 "Véhicules" => 
					 	array(	"Voiture", 
					 			), 

					 "Maison" => 
						 array(	 "Electroménager",
								 ),
					 "Loisirs" => 
						 array(	 "Sports", 
						 		 ),
	    			"Mode" => 
						 array(	 "Vêtements", 
								 )
	    			);

    	return $cat;
    }



    public static function getFreedomTags(){
    	$tags = array(
    		"all" 		=> array("label"=>"Tout", 				"key" => "all", 		"icon" => "circle-o", 				"section"=>1),
    		"like" 		=> array("label"=>"Coup de cœur", 		"key" => "like", 		"icon" => "heartbeat", 				"section"=>2),
    		"dislike" 	=> array("label"=>"Coup de gueule", 	"key" => "dislike", 	"icon" => "thumbs-o-down", 			"section"=>2),
    		"forsale" 	=> array("label"=>"À vendre", 			"key" => "forsale", 	"icon" => "money", 					"section"=>3),
    		"location" 	=> array("label"=>"À louer", 			"key" => "location", 	"icon" => "external-link", 			"section"=>3),
    		"donation" 	=> array("label"=>"À donner", 			"key" => "donation", 	"icon" => "gift", 					"section"=>4),
    		"sharing" 	=> array("label"=>"À partager", 		"key" => "sharing", 	"icon" => "exchange", 				"section"=>4),
    		"lookingfor" => array("label"=>"À la recherche", 	"key" => "lookingfor", 	"icon" => "eye", 					"section"=>5),
    		"job" 		=> array("label"=>"Offre d'emplois", 	"key" => "job", 		"icon" => "briefcase", 				"section"=>5),
    		"public" 	=> array("label"=>"Les communiqués", 	"key" => "public", 		"icon" => "bullhorn", 				"section"=>6),
    		"urgency" 	=> array("label"=>"Urgences", 			"key" => "urgency", 	"icon" => "exclamation-triangle", 	"section"=>6),
    				
    	);
    	return $tags;
    }
}
?>
