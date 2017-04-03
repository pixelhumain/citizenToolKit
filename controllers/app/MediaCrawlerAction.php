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

        //date_default_timezone_set('Pacific/Noumea'); //'Pacific/Noumea'

	    $res .= $this->extractSource("OUTREMERS360", "NEWS");
      $res .= $this->extractSource("NCTV", "YOUTUBE");

      $res .= $this->extractSource("NC1", "YOUTUBE");
      $res .= $this->extractSource("NC1", "NC");

      $res .= $this->extractSource("NCI", "EMISSION");
      $res .= $this->extractSource("NCI", "BLOG");

      $res .= $this->extractSource("TAZAR", "BLOG");
		
      /*
      //$res .= $this->extractSource("LNC", "BLOG");
      //$res .= $this->extractSource("CALEDOSPHERE", "FEED");
      */

    	$params = array("res" => $res);

    	echo $controller->renderPartial("mediacrawler", $params, true);
    }

    private function extractSource($src, $urlKey){
    	$result = "";

	    $mapExtract = $this->getMapExtraction($src, $urlKey);

		if($mapExtract == false){ echo "SOURCE NOT FOUND : ".$src." - ".$urlKey; return; }
			
		foreach($mapExtract["urls"] as $url) {
		
			error_log("CRAWLING ".$url);
            $html = file_get_html($url);
            //echo $html; exit;
            $uk = $html->find($mapExtract["elementUK"]);
            //echo "results ? ".(string)sizeof($uk); exit;
            if(sizeof($uk)>0)
            foreach($uk as $element) {
				$href 		= mb_convert_encoding(@$element->find(@$mapExtract["href"], 0)->href, "HTML-ENTITIES", "UTF-8");
				$img 		= mb_convert_encoding(@$element->find(@$mapExtract["img"], 0)->src, "HTML-ENTITIES", "UTF-8");
				$title 		= mb_convert_encoding(@$element->find(@$mapExtract["title"], 0)->plaintext, "HTML-ENTITIES", "UTF-8");
                $content    = mb_convert_encoding(@$element->find(@$mapExtract["content"], 0)->plaintext, "HTML-ENTITIES", "UTF-8");
                
                if($element->find(@$mapExtract["date"], 0)!= null)
				$date 		= mb_convert_encoding(@$element->find(@$mapExtract["date"], 0)->getAttribute("datetime"), "HTML-ENTITIES", "UTF-8");
				else $date = "";

                if($date == "" && $element->find(@$mapExtract["date"], 0)!= null)
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
          if(strpos($href, 'http')===0)                      
					if(@$mapExtract["followImg"] || @$mapExtract["followContent"] || @$mapExtract["followDate"]){
                        error_log("MediaCrawler : extracting ".$href);
                        $link = file_get_html($href);

                        if($link != false){
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
					}
					
					$date = $this->formatDate($src, $urlKey, $date);

					if($title != "" && $href != ""){
						$media = array( "title" 		=> @$title,
		                        "img" 			=> @$img,
		                        "date" 			=> @$date,
		                        "content" 	=> @$content,
		                        "href" 			=> @$href,

		                        "contentType" => @$mapExtract["type"],
		                        "srcMedia" 		=> @$src,
		                        "srcUrl" 		  => @$url,
		                        "urlKeyMedia" => @$urlKey,

		                        "dateImport"	=> new MongoDate(time()),
		                        "nbClick"		  => 0,
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
                        error_log("MediaCrawler : IGNORED (MISSING DATA) : ".$href);
                        
					}

			    }else{
			    	$result .= "URL IGNORED : ".$href."<br>";
                    error_log("MediaCrawler : IGNORED (media exists) : ".$href);
                        
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
    								array("urls" =>  array("http://www.nci.nc/category/emissions/"),
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
    								array("urls" =>  array("http://la1ere.francetvinfo.fr/nouvellecaledonie/"),
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
    								array("urls" =>  array("https://www.youtube.com/channel/UCu1v8ajo9Z-ZLBlR-QPmyCQ/videos?live_view=500&flow=grid&view=0&sort=dd"),
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
                        
              "TAZAR" => 
                  array( "BLOG" => 
                          array("urls" =>  array("http://www.tazar.nc/category/je-mimplique/",
                                                 "http://www.tazar.nc/category/je-me-bouge/",
                                                  "http://www.tazar.nc/category/demain-cest-moi/"
                                                  ),
                                "elementUK" => ".g1-collection-item",
                                "href" => ".entry-body .entry-title a",
                                "img" => ".entry-featured-media .wp-post-image",
                                "title" => ".entry-title",
                                "date" => ".entry-body .entry-date",
                                "content" => ".entry-summary p",
                                "type" => "text",
                                "followImg" => "#primary .entry-featured-media .wp-post-image",
                                "followContent" => "#primary .entry-content h3",
                                "followDate" => "#primary .entry-meta-wrap .entry-date",
                                "followDateIsDateTime" => true,
                                ),
                    ),
          
              "NCTV" => 
                  array( "YOUTUBE" => 
                          array("urls" =>  array("https://www.youtube.fr/channel/UCdX2FGu9cQ0HwKRnymkulcw/videos"),
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
              
              "OUTREMERS360" => 
                  array( "NEWS" => 
                          array("urls" =>  array("http://outremers360.com/bassins/pacifique/",
                                                 "http://outremers360.com/category/societe/",
                                                 "http://outremers360.com/category/planete/",
                                                 "http://outremers360.com/category/sciences/"),
                                "elementUK" => ".entry-list li.type-post",
                                "href" => ".entry-item .entry-title a",
                                "title" => ".entry-item .entry-title a",
                                "content" => ".entry-item .entry-content p",
                                "type" => "text",
                                "followImg" => ".entry-box .entry-thumb img",
                                "followDate" => ".entry-box .entry-date .date",
                                "followDateIsDateTime" => false,
                                ),
                      ),
              /*"CALEDOSPHERE" => 
                  array( "FEED" => 
                          array("urls" =>  array("https://caledosphere.com/flux-des-articles/"),
                                "elementUK" => "#Label1 .item",
                                "href" => "a.meta-item-date",
                                "img" => ".post_img a img",
                                "title" => ".post_header_title h5 a",
                                "date" => ".post_info_date a",
                                "content" => "p",
                                "type" => "text",
                                "followImg" => "#primary .wp-post-image",
                                "followContent" => ".entry-content h3",
                                "followDate" => "a.entry-date span.value",
                                "followDateIsDateTime" => false,
                                ),
                      ),*/
              /*"LNC" => 
                  array( "BLOG" => 
                          array("urls" =>  array(//"http://www.lnc.nc/infos-en-direct",
                                                  "http://www.lnc.nc/monde"
                                                ),
                                "elementUK" => "#block-system-main .views-row",
                                "href" => ".views-field-field-imagearticle a",
                                "img" => ".views-field-field-image-principale img",
                                "title" => ".views-field-title a",
                                "date" => ".views-field-created-1 span",
                                "content" => "",
                                "type" => "text",
                                //"followImg" => ".field-name-field-image-principale .content img",
                                //"followContent" => ".field-name-body p",
                                //"followDate" => ".node-content .published .date",
                                //"followDateIsDateTime" => false,
                                ),
                      ),*/
        		);

    	if(@$map[$src] && @$map[$src][$urlKey]) return $map[$src][$urlKey]; else return false;
    }

    //cette fonction est paramétrée pour fonctionner avec la timezone NC (+11h)
    private function formatDate($src, $urlKey, $date){ 

      date_default_timezone_set('Pacific/Noumea');

      error_log("DATE formatDate : ".$date." src:".$src." urlKey:".$urlKey." TIMZONE : ".date_default_timezone_get());

    	if($src == "NCI"){
            //$date = str_replace("+00:00", "+11:00", $date);
            //error_log("DATE formatDate NCI : ".$date." src:".$src." urlKey:".$urlKey." TIMZONE : ".date_default_timezone_get());

    		$date = new DateTime($date);

			return $date->format('Y-m-d H:i:s')."+11:00";;
    	}

    	if($src == "NC1" && $urlKey == "NC"){
          if(strpos($date, "aujourd'hui")!==false){
              $date = new DateTime();
          }else{
              $date = new DateTime($date);
          }
          return $date->format('Y-m-d H:i:s')."+11:00";
      }

      if($src == "LNC" && $urlKey == "BLOG"){
          
          $date = str_replace("à ", "", $date);
          $date = str_replace("Crée le ", "", $date);
          $date = new DateTime($date);
          
          return $date->format('Y-m-d H:i:s');//."+11:00";
      }

      if($src == "TAZAR" && $urlKey == "BLOG"){
          if(strpos($date, "aujourd'hui")!==false){
              $date = new DateTime();
          }else{
              $date = new DateTime($date);
          }
          return $date->format('Y-m-d H:i:s')."+11:00";
      }

      /*if($src == "CALEDOSPHERE"){
    		$date = html_entity_decode($date);
        //error_log("DDATE MONTH : ".$date);
    		$dateC = str_replace("Publié le ", "", $date);
    		$dateS = explode(" ", $dateC);
    		$month = $this->getMonthNum($dateS[1]);
        //error_log("DDATE MONTH : ".$month);
    		if($month == false) return false;
    		//else $month++;
    		
    		$date = $dateS[2]."-".$month."-".$dateS[0]." 00:00:00";
    		$date = new DateTime($date);
  		  return $date->format('Y-m-d H:i:s');
    	}*/


    	if($urlKey == "YOUTUBE"){
        //error_log("DATE YOUTUBE 1 : ".$date);
        $dateC = str_replace("Published on ", "", $date);
        $dateC = str_replace("Streamed live on ", "", $dateC);
        $dateC = str_replace(",", "", $dateC);

        if(strpos($dateC, "Streamed live ")!==false){
            $date = new DateTime();
            return $date->format('Y-m-d H:i:s')."+11:00";
        }

        $dateS = explode(" ", $dateC);
        $month = $this->getMonthNum($dateS[0]);
        //error_log("DATE YOUTUBE 2 : ".$month);
            
        if($month == false) return false;
        //else $month++;
        
   
        $date = $dateS[1]."-".$month."-".$dateS[2]." 00:00:00"; 
        $date = new DateTime($date);
        //echo $date->format('Y-m-d H:i:s'); //exit;
        return $date->format('Y-m-d H:i:s');
      }

      if($src == "OUTREMERS360"){
          //error_log("DATE OUTREMERS360 : ".$date);
          $dateS = explode(" ", $date);
          error_log("DATE OUTREMERS360 XXX : ".$dateS[1]);
          $month = $this->getMonthNum($dateS[1]);         
              
          $date = $dateS[2]."-".$month."-".$dateS[0]." 00:00:00"; 
          $date = new DateTime($date);
          //echo $date->format('Y-m-d H:i:s'); //exit;

          //error_log("DATE OUTREMERS360 : ".$date->format('Y-m-d H:i:s') );
          
          return $date->format('Y-m-d H:i:s');
      }

      return $date;
    }


    private function getMonthNum($monthName){ 
    	$months = array("janvier", "février", "mars", "avril", "mai", "juin", "juillet", 
                      "août", "septembre", "octobre", "novembre", "décembre");
      foreach($months as $key => $value){
        if($value == $monthName) return $key+1;
      }

      $months = array("janvier", "f&eacute;vrier", "mars", "avril", "mai", "juin", "juillet", 
                      "août", "septembre", "octobre", "novembre", "d&eacute;cembre");
      foreach($months as $key => $value){
        if($value == $monthName) return $key+1;
      }
          
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