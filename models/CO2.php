<?php 
class CO2 {

    public static function getThemeParams($domainName=null){

    	$domainName = @$domainName ? $domainName : Yii::app()->params["CO2DomainName"];
    	$params = array("CO2"=>array(
    						"title" => "CO2",

    						"pages" => 
    							array(
    								// "#"=>
    								// 	array("inMenu" => false, 
						      //                 "subdomain" => "", 
						      //                 "subdomainName" => "",
						      //                 "icon" => "search", 
						      //                 "mainTitle" => "",
						      //                 "placeholderMainSearch" => ""),
    								  
    								// "#co2.web"=>
    								//   	array("inMenu" => true, 
						      //                 "useHeader" => true, 
						      //                 "subdomain" => "web", 
						      //                 "subdomainName" => "web",
						      //                 "icon" => "search", 
						      //                 "mainTitle" => "Le moteur de recherche <span class='letter-green'>du green-web</span>",
						      //                 "placeholderMainSearch" => "rechercher sur le green web  ..."),

    								// "#co2.media"=>
    								//   	array("inMenu" => true, 
						      //                 "useHeader" => true, 
						      //                 "subdomain" => "media", 
						      //                 "subdomainName" => "media",
						      //                 "icon" => "newspaper-o", 
						      //                 "mainTitle" => "Toute l'actu <span class='letter-green'>des médias alternatifs</span>",
						      //                 "placeholderMainSearch" => "rechercher dans l'actu  ..."),

    								"#co2.social"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "social", 
						                      "subdomainName" => "social",
						                      "icon" => "user-circle-o", 
						                      "mainTitle" => "Le réseau social <span class='letter-green'>à effet de serre positif</span>",
						                      "placeholderMainSearch" => "Rechercher parmis les membres du réseau Communecter"),

    								"#co2.live"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "live", 
						                      "subdomainName" => "live",
						                      "icon" => "rss", 
						                      "mainTitle" => "",
						                      "placeholderMainSearch" => "rechercher dans le fil d'actualités"),

    								"#co2.agenda"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "agenda", 
						                      "subdomainName" => "agenda",
						                      "icon" => "hand-rock-o", 
						                      "mainTitle" => "L'agenda<span class='letter-green'>CO</span>mmun",
						                      "placeholderMainSearch" => "rechercher sur le green web  ..."),

    								"#co2.power"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "power", 
						                      "subdomainName" => "power",
						                      "icon" => "hand-rock-o", 
						                      "mainTitle" => "Un bien <span class='letter-green'>CO</span>mmun dédié à l'intelligence <span class='letter-green'>CO</span>llective",
						                      "placeholderMainSearch" => "rechercher sur le green web  ..."),

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
    								// "#"=>
    								// 	array("inMenu" => false, 
						      //                 "subdomain" => "", 
						      //                 "subdomainName" => "",
						      //                 "icon" => "search", 
						      //                 "mainTitle" => "Le moteur de recherche des Cagous",
						      //                 "placeholderMainSearch" => "rechercher sur le green web  ..."),
    								  
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
						                      "placeholderMainSearch" => "rechercher parmis les membres du réseau ..."),

    								"#co2.freedom"=>
    								  	array("inMenu" => true, 
						                      "useHeader" => true, 
						                      "subdomain" => "freedom", 
						                      "subdomainName" => "freedom",
						                      "icon" => "comments", 
						                      "mainTitle" => "Un espace d'expression libre pour tout les Calédoniens",
						                      "placeholderMainSearch" => "rechercher parmis les messages  ..."),

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

}
?>