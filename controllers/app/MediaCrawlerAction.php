<?php
/**
* retreive dynamically 
*/

include('protected/components/simple_html_dom.php');

class MediaCrawlerAction extends CAction
{
    public function run() {
    	$controller=$this->getController();
        
        $res = "";

		$res .= $this->extractSource("NCTV", "YOUTUBE");
		$res .= $this->extractSource("NC1", "YOUTUBE");
		$res .= $this->extractSource("NC1", "NC");
		$res .= $this->extractSource("CALEDOSPHERE", "FEED");
		$res .= $this->extractSource("NCI", "EMISSION");
		$res .= $this->extractSource("NCI", "BLOG");
		
    	$params = array("res" => $res);

    	echo $controller->renderPartial("mediacrawler", $params, true);
    }

    private function extractSource($src, $urlKey){
    	$result = "";

	    $mapExtract = $this->getMapExtraction($src, $urlKey);

		if($mapExtract == false) return "SOURCE NOT FOUND : ".$src." - ".$urlKey;
			
		foreach($mapExtract["urls"] as $url) {
		
			$html = file_get_html($url);
            error_log("CRAWLING ".$url);
			//echo $html; exit;
			foreach($html->find($mapExtract["elementUK"]) as $element) {

				$href 		= mb_convert_encoding(@$element->find(@$mapExtract["href"], 0)->href, "HTML-ENTITIES", "UTF-8");
				$img 		= mb_convert_encoding(@$element->find(@$mapExtract["img"], 0)->src, "HTML-ENTITIES", "UTF-8");
				$title 		= mb_convert_encoding(@$element->find(@$mapExtract["title"], 0)->plaintext, "HTML-ENTITIES", "UTF-8");
				$date 		= mb_convert_encoding(@$element->find(@$mapExtract["date"], 0)->getAttribute("datetime"), "HTML-ENTITIES", "UTF-8");
				$content 	= mb_convert_encoding(@$element->find(@$mapExtract["content"], 0)->plaintext, "HTML-ENTITIES", "UTF-8");

				if($date == "")
					$date 	= mb_convert_encoding(@$element->find($mapExtract["date"], 0)->plaintext, "HTML-ENTITIES", "UTF-8");
				
				$idYoutube = "";
				if($mapExtract["type"] == "youtube") {
					$href = "https://www.youtube.com".$href;
					$idYoutube = $this->exctractYoutubeId($href);
				}

				//si l'href existe déjà dans la base, on ne charge pas l'url
				$query = array('href' => $href);
				$mediaExists = PHDB::findOne("media", $query);
				
				if(!isset($mediaExists)){
					//var_dump($mediaExists); echo $href;
					if(@$mapExtract["followImg"] || @$mapExtract["followContent"] || @$mapExtract["followDate"]){
                        error_log("MediaCrawler : extracting ".$href);
						$link = file_get_html($href);

						if(@$mapExtract["followImg"]){
							$img = mb_convert_encoding(@$link->find($mapExtract["followImg"], 0)->src, "HTML-ENTITIES", "UTF-8");
						}
						if(@$mapExtract["followContent"])
							$content = mb_convert_encoding(@$link->find($mapExtract["followContent"], 0)->plaintext, "HTML-ENTITIES", "UTF-8");

						if(@$mapExtract["followDate"])
							if(@$mapExtract["followDateIsDateTime"] == true)
							$date = mb_convert_encoding(@$link->find($mapExtract["followDate"], 0)->getAttribute("datetime"), "HTML-ENTITIES", "UTF-8");
							else if(@$mapExtract["followDateIsDateTime"] == false)
							$date = mb_convert_encoding(@$link->find($mapExtract["followDate"], 0)->plaintext, "HTML-ENTITIES", "UTF-8");
							
					}
					
					$date = $this->formatDate($src, $urlKey, $date);

					if($title != "" && $href != ""){
						$media = array( "title" 		=> @$title,
				                        "img" 			=> @$img,
				                        "date" 			=> @$date,
				                        "content" 		=> @$content,
				                        "href" 			=> @$href,

				                        "contentType" 	=> @$mapExtract["type"],
				                        "srcMedia" 		=> @$src,
				                        "srcUrl" 		=> @$url,
				                        "urlKeyMedia" 	=> @$urlKey,

				                        "dateImport"	=> new MongoDate(time()),
				                        "nbClick"		=> 0,
			    						);

						if(@$mapExtract["type"] == "youtube") $media["idYoutube"] = $idYoutube;

						
				    	PHDB::insert("media", $media);

				    	$result .= "SRC:".$src.
							"<br>urlKey:".$urlKey.
							"<br>img : ".$img.
							"<br>href : ".$href.
							"<br>title : ".$title.
							"<br>date : ".$date.
							"<br>content : ".$content."<br>".
							"------------------------<br>";
					}else{
						$result .= "<br>URL IGNORED :: title :".$title." content : ".$content." href : ".$href."<br>";
                        error_log("MediaCrawler : IGNORED (MISSING DATA)".$href);
                        
					}

			    }else{
			    	$result .= "URL IGNORED : ".$href."<br>";
                    error_log("MediaCrawler : IGNORED (media exists)".$href);
                        
			    }

			}
		}

		return $result;
    }

    private function getMapExtraction($src, $urlKey){
    	$map = array(
    					"NCI" => 
    						array( "BLOG" => 
    								array("urls" =>  array("http://www.nci.nc/blog/", 
    												  	   "http://www.nci.nc/category/emissions/"),
    									  "elementUK" => ".cactus-post-item",
    									  "href" => ".picture .picture-content a",
    									  "img" => ".picture .picture-content a img",
    									  "title" => ".cactus-post-title a",
    									  "date" => ".posted-on .entry-date",
    									  "content" => ".excerpt",
    									  "type" => "text",
    									  ),

    								"EMISSION" => 
    								array("urls" =>  array("url" => "http://www.nci.nc/category/emissions/"),
    									  "elementUK" => ".cactus-post-item",
    									  "href" => ".picture .picture-content a",
    									  "img" => ".picture .picture-content a img",
    									  "title" => ".cactus-post-title a",
    									  "date" => ".posted-on .entry-date",
    									  "content" => ".excerpt",
    									  "type" => "video"
    									  ),
    							),
    					"NC1" => 
    						array( "NC" => 
    								array("urls" =>  array("url" => "http://la1ere.francetvinfo.fr/nouvellecaledonie/"),
    									  "elementUK" => ".block-fr3-content",
    									  "href" => ".content .image a",
    									  "img" => ".content .image a img",
    									  "title" => ".content .content-inner h2",
    									  "date" => ".date",
    									  "content" => ".content-inner .article-lien-content",
    									  "type" => "text",
    									  "followImg" => "#fr3-content-area .block-fr3-content-main-article .content .image img",
    									  "followContent" => "#fr3-content-area .block-fr3-content-main-article .content p",
    									  "followDate" => "#fr3-content-area .block-fr3-content-main-article .content .date .last time",
    									  "followDateIsDateTime" => true,
    									  ),

    								"YOUTUBE" => 
    								array("urls" =>  array("url" => "https://www.youtube.com/channel/UCu1v8ajo9Z-ZLBlR-QPmyCQ/videos"),
    									  "elementUK" => ".channels-content-item",
    									  "href" => ".yt-lockup-content a",
    									  "img" => "",
    									  "title" => ".yt-lockup-content a",
    									  "date" => ".yt-lockup-content a",
    									  "content" => ".watch-time-text",
    									  "type" => "youtube",
    									  "followContent" => "#watch-description-text",
    									  "followDate" => ".watch-time-text",
    									  "followDateIsDateTime" => false,
    									  ),
    							),
    					"CALEDOSPHERE" => 
    						array( "FEED" => 
    								array("urls" =>  array("url" => "https://caledosphere.com/flux-des-articles/"),
    									  "elementUK" => ".post_header",
    									  "href" => ".post_img a",
    									  "img" => ".post_img a img",
    									  "title" => ".post_header_title h5 a",
    									  "date" => ".post_info_date a",
    									  "content" => "p",
    									  "type" => "text",
    									  ),
    							),
    					"NCTV" => 
    						array( "YOUTUBE" => 
    								array("urls" =>  array("url" => "https://www.youtube.fr/channel/UCdX2FGu9cQ0HwKRnymkulcw/videos"),
    									  "elementUK" => ".channels-content-item",
    									  "href" => ".yt-lockup-content a",
    									  "img" => "",
    									  "title" => ".yt-lockup-content a",
    									  "date" => ".yt-lockup-content a",
    									  "content" => ".watch-time-text",
    									  "type" => "youtube",
    									  "followContent" => "#watch-description-text",
    									  "followDate" => ".watch-time-text",
    									  "followDateIsDateTime" => false,
    									  ),
    							),

        		);

    	if(@$map[$src] && @$map[$src][$urlKey]) return $map[$src][$urlKey]; else return false;
    }

    private function formatDate($src, $urlKey, $date){
    	if($src == "NCI"){
            $date = str_replace("+00:00", "+11:00", $date);
    		$date = new DateTime($date);
			return $date->format('Y-m-d H:i:s');
    	}

    	if($src == "NC1" && $urlKey == "NC"){
    		$date = new DateTime($date);
            
			return $date->format('Y-m-d H:i:s');
    	}

    	if($src == "CALEDOSPHERE"){
    		$date = html_entity_decode($date);
    		$dateC = str_replace("Publié le ", "", $date);
    		$dateS = explode(" ", $dateC);
    		$month = $this->getMonthNum($dateS[1]);

    		if($month == false) return false;
    		//else $month++;
    		
    		$date = $dateS[2]."-".$month."-".$dateS[0]." 00:00:00";
    		$date = new DateTime($date);
			return $date->format('Y-m-d H:i:s');
    	}


    	if($urlKey == "YOUTUBE"){
    		$dateC = str_replace("Published on ", "", $date);
    		$dateC = str_replace("Streamed live on ", "", $dateC);
    		$dateC = str_replace(",", "", $dateC);
    		$dateS = explode(" ", $dateC);
    		$month = $this->getMonthNum($dateS[0]);
           
          	if($month == false) return false;
    		//else $month++;
    		
            
    		$date = $dateS[1]."-".$month."-".$dateS[2]." 00:00:00"; 
    		$date = new DateTime($date);
    		//echo $date->format('Y-m-d H:i:s'); //exit;
			return $date->format('Y-m-d H:i:s');
    	}

    	return $date;
    }

    private function getMonthNum($monthName){ 
    	$months = array('janvier', "février", "mars", "avril", "mai", "juin", "juillet", "août", "septembre", "octobre", "novembre", "décembre");
    	foreach($months as $key => $value){
    		if($value == $monthName) return $key+1;
    	}
    	//date youtube
    	//echo $monthName."<br>";
        
    	$months = array('Jan', "Feb", "Mar", "Apr", "May", "June", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
    	foreach($months as $key => $value){
            if($value == $monthName) return $key+1;
    	}
    	return false;
    }

    private function exctractYoutubeId($href){
    	$i = strpos($href, "v=");
    	$id = substr($href, $i+2, strlen($href)-$i);
    	return $id; 
    }
}