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
				$str .= "<span class='text-red'>\n".
						"## I am a ".@$valueData["name"]."\n<br/> \n"
						."</span>\n";

				if(@$valueData["shortDescription"])
					$str .= "> ".@$valueData["shortDescription"]."<br/><br/>\n";
				if(@$valueData["description"])
					$str .= "> ".@$valueData["description"]."<br/><br/>\n\n";

				$str .= "### <i class='fa fa-user'></i> Information : "."\n".
						
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
					$str .=	"### <i class='fa fa-group'></i> Organizations : \n";
					foreach ($valueData["links"]["memberOf"] as $ix => $o) {
						$urlT = explode( "#", @$o["url"]["communecter"] );
						if(@$urlT[1]){
							$el =	"- <a href='#".$urlT[1]."' class='lbh'>".@$o["name"]."</a>";
							if(@$o["isAdmin"] == "true")
								$el .= "(Admin)";
							$str .=	$el."\n";
						}
					}
					$str .= "\n";
				}

				if( @$valueData["links"]["projects"] )
				{
					$str .=	"### <i class='fa fa-lightbulb-o'></i> Projects : \n";
					foreach ($valueData["links"]["projects"] as $ix => $o) {
						$urlT = explode( "#", @$o["url"]["communecter"] );
						if(@$urlT[1]){
							$el =	"- <a href='#".$urlT[1]."' class='lbh'>".@$o["name"]."</a>";if(@$o["isAdmin"])
							$el .= "(Admin)";
							$str .=	$el."\n";
						}
					}
					$str .= "\n";
				}

				if( @$valueData["links"]["events"] )
				{
					$str .=	"### <i class='fa fa-calendar'></i> Events : \n";
					foreach ($valueData["links"]["events"] as $ix => $o) {
						$urlT = explode( "#", @$o["url"]["communecter"] );
						if(@$urlT[1]){
							$el =	"- <a href='#".$urlT[1]."' class='lbh'>".@$o["name"]."</a>";if(@$o["isAdmin"])
							$el .= "(Admin)";
							$str .=	$el."\n";
						}
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