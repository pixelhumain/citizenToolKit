<?php

class Slug {
	const COLLECTION = "slugs";
	public static function save($type, $id, $slug){
		PHDB::insert(self::COLLECTION,
			array("id"=>$id,"type"=>$type,"name"=>$slug)
		);
		return true;
	}
	public static function update($type, $id, $slug){
		PHDB::update(self::COLLECTION,
			array("id"=>$id,"type"=>$type),
			array('$set'=>array("name"=>$slug))
		);
		return true;
	}
	public static function getByTypeAndId($type,$id){
		return PHDB::findOne(self::COLLECTION,array("type"=>$type,"id"=>$id));
	}
	public static function check($params){
		$res=PHDB::findOne(self::COLLECTION,array("name"=>$params["slug"], "id"=>array('$ne'=>$params["id"])));
		if(!empty($res))
			return false;
		else 
			return true;
	}
	public static function checkAndCreateSlug($str,$type,$id){
		$unwanted_array = array(    
			'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
			'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
		$res="";
		$value=explode(" ",$str);
		$i=0;
		foreach($value as $v){
			$text = strtr( $v, $unwanted_array );
			//$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
			$text = preg_replace('~[^\\pL\d]+~u', '', $text);
  			// trim
  			$text = trim($text, '-');
 			// transliterate
  			$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
  			// lowercase
  			$text = strtolower($text);
  			if($i>0)
  				$text = ucfirst($text);
  			// remove unwanted characters
  			$text = preg_replace('~[^-\w]+~', '', $text);
  			$res.=$text;
  			$i++;
		}	
		if(!self::check(array("slug"=>$res,"type"=>$type,"id"=>$id))){
			$v = 1; // $i est un nombre que l'on incrémentera. 
			$inc=false;
			while($inc==false) 
			{ 
				$inc=self::check(array("slug"=>$res.$v,"type"=>$type,"id"=>$id));
				if($inc)
					$res=$res.$v;
				else
			  		$v ++ ;
			}
		}
		return $res;
	}
}

