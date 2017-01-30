<?php
class InitDataNetworkMappingAction extends CAction
{
    public function run()
    {
        //liste des tags de rangements de l'asso
		$tags_rangements = array(
								array(	"name" => "Citoyens", 
										"ico" => "male", 
										"color" => "yellow"),
										
								array(	"name" => "Entreprises", 
										"ico" => "briefcase", 
										"color" => "blue"),
										
								array(	"name" => "Collaborateurs", 
										"ico" => "circle", 
										"color" => "purple"),
										
								array(	"name" => "Chercheurs", 
										"ico" => "male", 
										"color" => "green")
							);
		
		//liste des tags de rangements de chaque membre
		$tagsR = array( "Citoyens", "Citoyens", "Citoyens", "Entreprises", "Entreprises", 
						"Collaborateurs", "Chercheurs", "Chercheurs", "Chercheurs", "Chercheurs");
		
		$where = array('geo' => array( '$exists' => true ));				
    	$citoyens = PHDB::findAndSort(Person::COLLECTION, $where, array('name' => 1), 10);
    	
    	$membres = array(); $i = 0;
    	foreach($citoyens as $citoyen){
    		$membres[] = array("id" => $citoyen["_id"], "tag_rangement" => $tagsR[$i]);
    		$i++;
    	}
    	
    	$where = array(	'_id'  => new MongoId(Yii::app()->session["userId"]) );
	 	$me = PHDB::findOne(Person::COLLECTION, $where);
    	
    	
    	$newAsso = array(
    	
        "@context" => array("@vocab" => "http://schema.org",
        					"ph" => "http://pixelhumain.com/ph/ontology/"
    						),
   		"email" => $me["email"],
    	"name" => "asso1",
    	"type" => "association",
    	"cp" => "75000",
    	"address" => array(	"@type" => "PostalAddress",
        					"postalCode" => "75000",
        					"addressLocality" => "France"
    					),
   		"description" => "Description de l'association",
    	"tags_rangement" => $tags_rangements,
    	"membres" => $membres
    	
    	);
    	
    	$res = PHDB::insert(Organization::COLLECTION, $newAsso);
    	
    	if($res["ok"] == 1) $res = "Initialisation des données : OK</br>Rechargez la carte !";
        Rest::json( $res );
        Yii::app()->end();
    }
}