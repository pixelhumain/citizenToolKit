<?php
class ShowNetworkMappingAction extends CAction
{
    public function run()
    {
        //début de la requete => scope geographique
    	$where = array( 'name' => $_POST["assoName"] );
		$asso = PHDB::findOne(Organization::COLLECTION, $where);
        
     	$orgaMembres = array();
    	
		foreach($asso["membres"] as $membre){
			if(in_array($membre["tag_rangement"], $_POST["types"])){
				$where = array( 'geo'  => array( '$exists' => true ),
								'_id' => new MongoId($membre["id"]) );
			
				$newMembre = PHDB::findOne(Person::COLLECTION, $where);
				$newMembre["type"] = $membre["tag_rangement"];
					
				foreach($asso["tags_rangement"] as $tagR){
					if($membre["tag_rangement"] == $tagR["name"]){
						$newMembre["ico"] = $tagR["ico"];
						$newMembre["color"] = $tagR["color"];
						break;
					}
				}
				$orgaMembres[] = $newMembre;
			}
		}
    	
    	Rest::json( $orgaMembres );
        Yii::app()->end();
    }
}