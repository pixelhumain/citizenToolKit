<?php 
class TranslateTree {
/*

	----------------- COMMUNECTER ----------------- 
*/
	

	public static function build($data,$type)
	{
		
		$res = array(
			"name" => $type,
			"children" => array()
		);
		$ct = 0;
		foreach ($data as $keyID => $valueData) {

			if ( isset($valueData) ) {
				

				$res["children"][$ct] = array(
						"name" => "I am a ".@$valueData["name"],
						"children" => array()
				);

				$res["children"][$ct]["children"][] = array(
						"name" => "exports",
						"children" => array(
							array( "name" => "markdown :: http://communecter.org/api/".$type."/get/id/".$keyID."/format/md")
						)
				);
				
				$information = array(
					array( "name" => "my ID :".$keyID ),
					array( "name" => "email : ".@$valueData["email"]),
					array( "name" => "username : ".@$valueData["username"]),
					array( "name" => "country : ".@$valueData["address"]["addressCountry"]),
					array( "name" => "city : ".@$valueData["address"]["addressLocality"]),
					array( "name" => "postalCode : ".@$valueData["address"]["postalCode"]),
					array( "name" => "see in map : http://www.openstreetmap.org/?mlat=".@$valueData["geo"]["latitude"]."&mlon=".@$valueData["geo"]["longitude"]."&zoom=12")
				);
				
				if(@$valueData["shortDescription"])
					$information[] = array( "name" => "shortDescription".@$valueData["shortDescription"], 					"size" => 3000
						);
				if(@$valueData["description"])
					$information[] = array( "name" => "description".@$valueData["description"],
											"size" => 3000);
				
				$res["children"][$ct]["children"][] = array(
						"name" => "information",
						"children" => $information
				);

				$str = "";
				if( @$valueData["links"]["memberOf"] )
				{
					$elements = array();	
					foreach ($valueData["links"]["memberOf"] as $ix => $o) {
						$el = array( "name" => @$o["name"],
									 "url"=> @$o["url"]["communecter"] );
						$elements[] = $el;
					}
					$res["children"][$ct]["children"][] = array(
							"name" => "Organizations",
							"children" => $elements
					);
				}

				if( @$valueData["links"]["projects"] )
				{
					$elements = array();	
					foreach ($valueData["links"]["projects"] as $ix => $o) {
						$el = array( "name" => @$o["name"],
									 "url"=> @$o["url"]["communecter"] );
						$elements[] = $el;
					}
					$res["children"][$ct]["children"][] = array(
							"name" => "Projects",
							"children" => $elements
					);
				}

				if( @$valueData["links"]["events"] )
				{
					$elements = array();	
					foreach ($valueData["links"]["events"] as $ix => $o) {
						$el = array( "name" => @$o["name"],
									 "url"=> @$o["url"]["communecter"] );
						$elements[] = $el;
					}
					$res["children"][$ct]["children"][] = array(
							"name" => "Events",
							"children" => $elements
					);
				}
				
					/*	"# Points of interests"."\n".
						"# Ressources : "."\n".
						"# Places : "."\n".
						"# Classifieds : "; */
			}
		}
		return $res;
	}

}