<?php 
class TranslateMD {
/*

	----------------- COMMUNECTER ----------------- 
*/
	

	public static function MD($data,$type)
	{
		

		foreach ($data as $keyID => $valueData) {

			if ( isset($valueData) ) {
				$str = "[<i class='fa fa-link'></i> Markdown Source](http://communecter.org/api/".$type."/get/id/".$keyID."/format/md) \n\n\n";
				$str .= "## I am a ".@$valueData["name"]."\n";

				if(@$valueData["shortDescription"])
					$str .= "> ".@$valueData["shortDescription"]."\n";
				if(@$valueData["description"])
					$str .= "> ".@$valueData["description"]."\n\n";
				$str .= "### Information : "."\n".
						
						"- my ID :".$keyID."\n".
						"- email : ".@$valueData["email"]."\n".
						"- username : ".@$valueData["username"]."\n".
						
						//"### Where : "."\n".
						"- country : ".@$valueData["address"]["addressCountry"]."\n".
						"- city : ".@$valueData["address"]["addressLocality"]."\n".
						//"- email : ".@$valueData["email"]."\n".
						"- postalCode : ".@$valueData["address"]["postalCode"]."\n".
						"- [url]( http://www.openstreetmap.org/?mlat=".@$valueData["geo"]["latitude"]."&mlon=".@$valueData["geo"]["longitude"]."&zoom=12 )\n".
						"\n";


				if( @$valueData["links"]["memberOf"] )
				{
					$str .=	"### Organizations : \n";
					foreach ($valueData["links"]["memberOf"] as $ix => $o) {
						$el =	"- [".@$o["name"]."](http://communecter.org#".@$o["url"]["api"]."/format/md)";
						if(@$o["isAdmin"])
							$el .= "(Admin)";
						$str .=	$el."\n";
					}
					$str .= "\n";
				}

				if( @$valueData["links"]["projects"] )
				{
					$str .=	"### Projects : \n";
					foreach ($valueData["links"]["projects"] as $ix => $o) {
						$el =	"- [".@$o["name"]."](http://communecter.org#".@$o["url"]["api"]."/format/md)";
						if(@$o["isAdmin"])
							$el .= "(Admin)";
						$str .=	$el."\n";
					}
					$str .= "\n";
				}

				if( @$valueData["links"]["events"] )
				{
					$str .=	"### Events : \n";
					foreach ($valueData["links"]["events"] as $ix => $o) {
						$el =	"- [".@$o["name"]."](http://communecter.org#".@$o["url"]["api"]."/format/md)";
						if(@$o["isAdmin"])
							$el .= "(Admin)";
						$str .=	$el."\n";
					}
					$str .= "\n";
				}
				
					/*	"# Points of interests"."\n".
						"# Ressources : "."\n".
						"# Places : "."\n".
						"# Classifieds : "; */
			}
		}
		return $str;
	}

}